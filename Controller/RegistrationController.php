<?php

namespace Tom32i\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Tom32i\UserBundle\Form\UserType;

class RegistrationController extends Controller
{
	/**
     * @Route("/register", name="register")
     * @Template()
     */
    public function registerAction()
    {
        $className = $this->container->getParameter('user_class');
	    $user = new $className;
    	$form = $this->createForm(new UserType(), $user);
    	$request = $this->getRequest();

        if ('POST' === $request->getMethod()) 
        {
		    $form->bindRequest($request);

		    if ($form->isValid()) 
		    {
	    		$em = $this->getDoctrine()->getEntityManager();

	    		$user->updatePassword($this->get('security.encoder_factory'));
	    		$user->setEnabled(true);

		        $em->persist($user);
		        $em->flush();

		        $this->authenticateUser($user);

		        return $this->redirect($this->generateUrl('homepage'));
		    }
        }

    	return array(
    		'form' => $form->createView()
    	);
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
}