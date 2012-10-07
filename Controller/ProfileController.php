<?php

namespace Tom32i\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Tom32i\UserBundle\Form\Model\PasswordRequest;
use Tom32i\UserBundle\Form\ProfileType;
use Tom32i\UserBundle\Form\DeleteType;
use Tom32i\UserBundle\Form\PasswordRequestType;
use Symfony\Component\Form\FormError;

class ProfileController extends Controller
{
    /**
     * @Route("/profile", name="profile_edit")
     * @Template()
     */
    public function editAction()
    {
        $user = $this->getUser();
        $currentMail = $user->getEmail();

        $form_type = $this->container->get($this->container->getParameter('tom32i_user.form.profile'));
        $form = $this->createForm($form_type, $user);
        $request = $this->getRequest();

        if ('POST' === $request->getMethod()) 
        {
            $form->bindRequest($request);

            if ($form->isValid()) 
            {
                $em = $this->getDoctrine()->getEntityManager();

                $user->onProfileEdit();

                $passwordChanged = $user->updatePassword($this->get('security.encoder_factory'));

                $em->persist($user);
                $em->flush();

                if(!$passwordChanged)
                {
                    $form->get('currentPassword')->addError(new FormError("Wrong value for current password."));
                }

                $newMail = $user->getEmail();
                $mailChanged = $currentMail != $newMail && !empty($newMail);

                if($mailChanged)
                {
                    $this->sendEmailConfirmation($user);
                }

                //return $this->redirect($this->generateUrl('profile_edit'));
            }
        }

        return array(
            'form' => $form->createView()
        );
    }

    /**
     * @Route("/profile/delete", name="profile_delete")
     * @Template()
     */
    public function deleteAction()
    {
        $user = $this->getUser();
        $form = $this->createForm($this->container->get('tom32i_user.delete.form.type'), $user);
        $request = $this->getRequest();

        if ('POST' === $request->getMethod()) 
        {
            $form->bindRequest($request);

            if ($form->isValid()) 
            {
                $encoder = $this->get('security.encoder_factory')->getEncoder($user);
                $password = $user->getPassword();

                $valid = empty($password) ? true : $encoder->isPasswordValid($password, $user->currentPassword, $user->getSalt());

                if($valid)
                {
                    $em = $this->getDoctrine()->getEntityManager();

                    $em->remove($user);
                    $em->flush();

                    return $this->redirect($this->generateUrl($this->container->getParameter('tom32i_user.redirection.delete')));
                }
                else
                {
                    $form->get('currentPassword')->addError(new FormError("Wrong value for current password."));
                }
            }
        }

        return array(
            'form' => $form->createView()
        );
    }

    /**
     * @Route("/password/request", name="request_password")
     * @Template("Tom32iUserBundle:Profile:request_password.html.twig")
     */
    public function requestPasswordAction()
    {
        $data = new PasswordRequest();
        $form = $this->createForm(new PasswordRequestType(), $data);
        $request = $this->getRequest();

        if ('POST' === $request->getMethod()) 
        {
            $form->bindRequest($request);

            if ($form->isValid()) 
            {
                $em = $this->getDoctrine()->getEntityManager();
                $user = $em->getRepository($this->container->getParameter('tom32i_user.user_class'))->findOneBy(array('email' => $data->email));

                if (!$user) 
                {
                    $form->get('email')->addError(new FormError("Sorry, we don't know this email address."));
                }
                else
                {
                    $this->sendPasswordConfirmation($user);

                    return array(
                        'data' => $data
                    );
                }
            }
        }

        return array(
            'form' => $form->createView()
        );
    }

    /**
     * @Route("/profile/resend-confirmation-email", name="resend_confirmation_email")
     * @Template()
     */
    public function resendConfirmEmailAction()
    {
        $user = $this->getUser();
        $valid = $user->isEmailValid();

        if(!$valid)
        {
            $this->sendEmailConfirmation($user);

            $this->get('session')->getFlashBag()->add('success', "We just sent you another comfirmation email. Hopefuly you'll get this one ;).");
        }

        return $this->redirect($this->generateUrl('profile_edit'));
    }

    private function sendEmailConfirmation(&$user)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $user->resetEmail();

        $em->persist($user);
        $em->flush();

        $mailer = $this->get('tom32i_user_mailer');
        $mailer->sendToUser($user, 'Confirm your email adress', 'confirmation_email');
    }

    private function sendPasswordConfirmation(&$user)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $user->resetPassword();

        $em->persist($user);
        $em->flush();

        $mailer = $this->get('tom32i_user_mailer');
        $mailer->sendToUser($user, 'Choose your new password', 'confirmation_password');
    }
}