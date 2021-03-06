<?php

namespace Tom32i\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Tom32i\UserBundle\Entity\User;
use Tom32i\UserBundle\Entity\Confirmation;
use Tom32i\UserBundle\Form\PasswordResetType;
use Symfony\Component\Form\FormError;

class ConfirmationController extends Controller
{
    /**
     * @Route("/comfirm-email/{token}", name="confirmation_email")
     * @Template()
     */
    public function confirmEmailAction($token)
    {
    	$em = $this->getDoctrine()->getEntityManager();

        $user = $em->getRepository($this->container->getParameter('tom32i_user.user_class'))->findOneBy(array('confirmationToken' => $token));

        if($user && $user->isConfirmationEmailValid()) 
        {
    		$user->setEmailValid(true);
    		$em->persist($user);
            $em->flush();

            $this->authenticateUser($user);

            $this->get('session')->getFlashBag()->add('success', "Congratulations! Your email address has been validated.");
        }
        else
        {
            $this->get('session')->getFlashBag()->add('error', "We couldn't confirm your email address, the link you used is expired. Please try again.");
        }

        return $this->redirect($this->generateUrl($this->container->getParameter('tom32i_user.redirection.emailConfirmed')));
    }

    /**
     * @Route("/password/reset/{token}", name="reset_password")
     * @Template("Tom32iUserBundle:Confirmation:reset_password.html.twig")
     */
    public function resetPasswordAction($token)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $user = $em->getRepository($this->container->getParameter('tom32i_user.user_class'))->findOneBy(array('confirmationToken' => $token));

        if ($user
            && is_a($user, $this->container->getParameter('tom32i_user.user_class')) 
            && $user->isValid() 
            && $user->isConfirmationPasswordValid() 
        ) 
        {
            $form = $this->createForm($this->get('tom32i_user.password_reset.form.type'), $user);
            $request = $this->getRequest();

            if ('POST' === $request->getMethod()) 
            {
                $form->bindRequest($request);

                if ($form->isValid()) 
                {
                    $user->setPassword(null);
                    $user->updatePassword($this->get('security.encoder_factory'));

                    $em->persist($user);
                    $em->flush();

                    $this->authenticateUser($user);

                    return $this->redirect($this->generateUrl($this->container->getParameter('tom32i_user.redirection.passwordReset')));
                }
            }

            return array(
                'form' => $form->createView(),
                'token' => $token,
            );
        }

        $this->get('session')->getFlashBag()->add('error', "We can't let you change your password, the link you used is expired. Please try again.");

        return $this->redirect($this->generateUrl('login'));
    }

    /**
     * Authenticate a user with Symfony Security
     *
     * @param \Tom32i\UserBundle\Entity\User $user
     */
    private function authenticateUser($user)
    {
        // Create the authentication token
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        // Give it to the security context
        $this->container->get('security.context')->setToken($token);
    }
}