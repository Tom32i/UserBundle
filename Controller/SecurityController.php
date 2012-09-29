<?php

namespace Tom32i\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class SecurityController extends Controller
{
    /**
     * @Route("/login", name="login")
     * @Template()
     */
    public function loginAction()
    {
        $current_user = $this->getUser();

        if ($current_user && is_a($current_user, $this->container->getParameter('tom32i_user.user_class')) && $current_user->isValid())
        {
            return $this->redirect($this->generateUrl($this->getParameter('tom32i_user.redirection.login')));
        }
        
    	$request = $this->getRequest();
        $session = $request->getSession();

        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) 
        {
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } 
        else 
        {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $session->remove(SecurityContext::AUTHENTICATION_ERROR);
        }

        return array(
            'last_username' => $session->get(SecurityContext::LAST_USERNAME),
            'error'         => $error,
        );
    }

    /**
     * @Route("/login_check", name="login_check")
     * @Template()
     */
    public function loginCheckAction()
    {
        return array();
    }

    /**
     * @Route("/logout", name="logout")
     * @Template()
     */
    public function logoutAction()
    {
        return array();
    }
}
