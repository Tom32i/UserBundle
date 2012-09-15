<?php

namespace Tom32i\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Tom32i\UserBundle\Model\TwitterOAuth;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class TwitterController extends Controller
{
    /**
     * @Route("/login", name="twitter_login")
     * @Template()
     */
    public function loginAction()
    {
        $current_user = $this->getUser();

        if ($current_user && is_a($current_user, $this->container->getParameter('user_class')) && $current_user->isValid())
        {
            return $this->redirect($this->generateUrl('profile_edit'));
        }

    	return $this->authenticate('login');
    }

    /**
     * @Route("/register", name="twitter_register")
     * @Template()
     */
    public function registerAction()
    {
        $current_user = $this->getUser();

        if ($current_user && is_a($current_user, $this->container->getParameter('user_class')) && $current_user->isValid())
        {
            return $this->redirect($this->generateUrl('profile_edit'));
        }

    	return $this->authenticate('register');
    }

    /**
     * @Route("/link", name="twitter_link")
     * @Template()
     */
    public function linkAction()
    {
        return $this->authenticate('link');
    }

    /**
     * @Route("/unlink", name="twitter_unlink")
     * @Template()
     */
    public function unlinkAction()
    {
        $current_user = $this->getUser();

        if ($current_user && is_a($current_user, $this->container->getParameter('user_class')) && $current_user->isValid())
        {

            $current_user->resetTwitter();

            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($current_user);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('profile_edit'));
    }

    /**
     * @Route("/callback/{action}", requirements={"action" = "login|register|link"}, name="twitter_callback")
     * @Template()
     */
    public function callbackAction($action)
    {
    	$session = $this->get('session');
    	$oauth_token = $this->request('oauth_token');

		/* If the oauth_token is old redirect to the connect page. */
		if ($oauth_token && $session->get('oauth_token') !== $oauth_token) 
		{
			return $this->fail($action);
		}

		/* Create TwitteroAuth object with app key/secret and token key/secret from default phase */
		$connection = new TwitterOAuth(
			$this->container->getParameter('twitter_consumer_key'), 
			$this->container->getParameter('twitter_consumer_secret'), 
			$session->get('oauth_token'), 
			$session->get('oauth_token_secret')
		);

		/* Request access tokens from twitter */
		$access_token = $connection->getAccessToken($this->request('oauth_verifier'));

		/* Save the access tokens. Normally these would be saved in a database for future use. */
		$session->set('access_token', $access_token);

		/* Remove no longer needed request tokens */
		$session->set('oauth_token', null);
		$session->set('oauth_token_secret', null);

		/* If HTTP response is 200 continue otherwise send to connect page to retry */
		if (200 == $connection->http_code) 
		{
			switch ($action) 
			{
				case 'register':
					return $this->registerUser($connection, $access_token);
					break;
				
				case 'login':
					return $this->logUser($connection, $access_token);
					break;
                
                case 'link':
                    return $this->linkUser($connection, $access_token);
                    break;
			}
		} 

		return $this->fail($action);
    }

    private function linkUser($connection, $access_token)
    {
        $current_user = $this->getUser();

        if ($current_user && is_a($current_user, $this->container->getParameter('user_class')) && $current_user->isValid())
        {
            $em = $this->getDoctrine()->getEntityManager();
            $user = $this->getUserFromTwitter($access_token['user_id']);

            if($user)
            {
                return $this->fail('link','This Twitter account is already linked to an account.');
            }

            $user_data = $connection->getUser($access_token['user_id']);

            $current_user->fillFromTwitterToken($access_token);
            $current_user->fillFromTwitter($user_data);
            $current_user->setImageTwitter($user_data);

            $em->persist($current_user);
            $em->flush();

            return $this->redirect($this->generateUrl('profile_edit'));
        }

        return $this->fail($action);
    }

    private function logUser($connection, $access_token)
    {
        $user = $this->getUserFromTwitter($access_token['user_id']);

        if($user)
        {
        	$this->authenticateUser($user);
	        return $this->redirect($this->generateUrl('homepage'));
        }

        $register = $this->generateUrl('twitter_register');
        $msg = "This twitter account is not register yet. Would you like to ";
        $msg .= '<a href="'. $register .'">register</a>?';

        $this->get('session')->getFlashBag()->add('error', $msg);

        return $this->redirect($this->generateUrl('login'));
    }

    private function registerUser($connection, $access_token)
    {
    	$user_data = $connection->getUser($access_token['user_id']);

        $user = $this->getUserFromTwitter($access_token['user_id']);

        if($user)
        {
            $this->authenticateUser($user);
            return $this->redirect($this->generateUrl('homepage'));
        }

        $em = $this->getDoctrine()->getEntityManager();
        $um = $em->getRepository($this->container->getParameter('user_class'));

        $username = $user_data->screen_name;
        $num = 1;

        while ($um->findByUsername($username)) 
        {
            $username = $user_data->screen_name . ' ' . $num;
            $num ++;
        }

		$user = new $this->container->getParameter('user_class');

        $user->setUsername($username);
        $user->setEnabled(true);

    	$user->fillFromTwitterToken($access_token);
    	$user->fillFromTwitter($user_data);
        $user->updateCanonicalFields();

        $em->persist($user);
        $em->flush();

        $user->setImageTwitter($user_data);

        $em->persist($user);
        $em->flush();

        $this->authenticateUser($user);

        return $this->redirect($this->generateUrl('homepage'));
    }

    private function request($varname)
    {
    	$request = $this->getRequest();
    	$post = $request->request->get($varname, null);

		return $post !== null ? $post : $request->query->get($varname);
    }

    private function authenticate($action)
    {
    	$session = $this->get('session');

    	/* Build TwitterOAuth object with client credentials. */
		$connection = new TwitterOAuth(
			$this->container->getParameter('twitter_consumer_key'), 
			$this->container->getParameter('twitter_consumer_secret')
		);
		 
		/* Get temporary credentials. */
		$callback = $this->generateUrl('twitter_callback', array('action' => $action), true);
		$request_token = $connection->getRequestToken($callback);
		$token = $request_token['oauth_token'];

		/* Save temporary credentials to session. */
		$session->set('oauth_token', $token);
		$session->set('oauth_token_secret', $request_token['oauth_token_secret']);
		 
		/* If last connection failed don't display authorization link. */
		switch ($connection->http_code) 
		{
			case 200:
				/* Build authorize URL and redirect user to Twitter. */
				return $this->redirect($connection->getAuthorizeURL($token));
				break;

			default:
				return $this->fail($action);
		}
    }

    private function fail($action, $msg = 'Could not connect to Twitter. Refresh the page or try again later.')
    {
    	$session = $this->get('session');

		$session->set('oauth_token', null);
		$session->set('oauth_token_secret', null);
		$session->set('access_token', null);

        $session->getFlashBag()->add('error', $msg);

        switch ($action) 
        {
            case 'link':

                return $this->redirect($this->generateUrl('profile_edit'));
            
            default:

                return $this->redirect($this->generateUrl($action));
        }
    }

    /**
     * Authenticate a user with Symfony Security
     *
     * @param \Tom32i\UserBundle\Entity\User       $user
     */
    private function authenticateUser($user)
    {
        // Create the authentication token
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        // Give it to the security context
        $this->container->get('security.context')->setToken($token);
    }

    private function getUserFromTwitter($uid)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $user = $em->getRepository($this->container->getParameter('user_class'))->findOneBy( array( 'twitterUserId' => $uid) );

        return ($user && is_a($user, $this->container->getParameter('user_class')) && $user->isValid()) ? $user : false;
    }
}
