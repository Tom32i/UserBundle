<?php

namespace Tom32i\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Tom32i\UserBundle\Entity\User;
use Tom32i\UserBundle\Entity\Confirmation;
use Tom32i\SiteBundle\Controller\SuperController;
use Tom32i\UserBundle\Form\PasswordResetType;
use Symfony\Component\Form\FormError;

class ConfirmationController extends SuperController
{
    /**
     * @Route("/comfirm-email/{token}", name="confirmation_email")
     * @Template()
     */
    public function confirmEmailAction($token)
    {
    	$em = $this->getDoctrine()->getEntityManager();

        $confirmation = $em->getRepository('Tom32iUserBundle:Confirmation')->findOneBy(array('token' => $token));

        if ($confirmation) 
        {
            $current_user = $this->getcurrentUser();
            $user = $confirmation->getUser();

            if(	$confirmation->isValid(Confirmation::ACTION_EMAIL) 
            	&& $current_user 
                && is_a($current_user, 'Tom32i\UserBundle\Entity\User') 
            	&& $current_user->isValid() 
            	&& $current_user->isEqualTo($user)
            ) 
            {
                $em->remove($confirmation);
        		$user->setEmailValid(true);
        		$em->persist($user);
                $em->flush();

                $this->get('session')->getFlashBag()->add('success', "Congratulations! Your email address has been validated.");
            }
            else
            {
                $this->get('session')->getFlashBag()->add('error', "We couldn't confirm your email address, the link you used is expired. Please try again.");
                $em->remove($confirmation);
                $em->flush();
            }
        }
        else
        {
            $this->get('session')->getFlashBag()->add('error', "We couldn't confirm your email address, the link you used is expired. Please try again.");
        }

        return $this->redirect($this->generateUrl('profile_edit'));
    }

    /**
     * @Route("/password/reset/{token}", name="reset_password")
     * @Template("Tom32iUserBundle:Confirmation:reset_password.html.twig")
     */
    public function resetPasswordAction($token)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $confirmation = $em->getRepository('Tom32iUserBundle:Confirmation')->findOneBy(array('token' => $token));

        if ($confirmation) 
        {
            $user = $confirmation->getUser();

            if( $confirmation->isValid(Confirmation::ACTION_PASSWORD) 
                && $user 
                && is_a($user, 'Tom32i\UserBundle\Entity\User') 
                && $user->isValid() 
            ) 
            {
                $form = $this->createForm(new PasswordResetType(), $user);
                $request = $this->getRequest();

                if ('POST' === $request->getMethod()) 
                {
                    $form->bindRequest($request);

                    if ($form->isValid()) 
                    {
                        $user->setPassword(null);
                        $user->updatePassword($this->get('security.encoder_factory'));

                        $em->remove($confirmation);
                        $em->persist($user);
                        $em->flush();

                        $this->authenticateUser($user);

                        return $this->redirect($this->generateUrl('profile_edit'));
                    }
                }

                return array(
                    'form' => $form->createView(),
                    'token' => $token,
                );
            }
            else
            {
                $em->remove($confirmation);
                $em->flush();
            }
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