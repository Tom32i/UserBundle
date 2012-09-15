<?php

namespace Tom32i\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Tom32i\UserBundle\Entity\User;
use Tom32i\UserBundle\Entity\Confirmation;
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

        $form = $this->createForm(new ProfileType(), $user);
        $request = $this->getRequest();

        if ('POST' === $request->getMethod()) 
        {
            $form->bindRequest($request);

            if ($form->isValid()) 
            {
                $em = $this->getDoctrine()->getEntityManager();

                $image = $user->getImage();
        
                if ($image->file === null)
                {
                    $image_id = $image->getId();
                    if ($image_id === null)
                    {
                        $entity->removeImage();
                    }
                }
                else
                {
                    $image->upload('users/'.$user->getId(), null, 'avatar');
                } 

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
        $form = $this->createForm(new DeleteType(), $user);
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

                    $boards = $user->getBoards()->toArray();
                    $boards[] = $user->getFavBoard();

                    foreach($boards as $board)
                    {
                        $em->remove($board);
                    }

                    $em->remove($user);
                    $em->flush();

                    return $this->redirect($this->generateUrl('homepage'));
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
                $user = $em->getRepository('Tom32iUserBundle:User')->findOneBy(array('email' => $data->email));

                if (!$user) 
                {
                    $form->get('email')->addError(new FormError("Sorry, we don't know the email address."));
                }
                else
                {
                    $confirmation = new Confirmation($user, Confirmation::ACTION_PASSWORD);

                    $em->persist($confirmation);
                    $em->flush();

                    $content = $this->renderView('Tom32iUserBundle:Email:confirmation_password.html.twig', array(
                        'confirmation' =>  $confirmation,
                        'user' => $user,
                    ));

                    $message = \Swift_Message::newInstance()
                        ->setSubject('Keeptools | Choose your new password')
                        ->setFrom('noreply@keeptools.com')
                        ->setTo(array($data->email => $user->name()))
                        ->setBody($content, 'text/html')
                    ;
                    $this->get('mailer')->send($message);

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
            $em = $this->getDoctrine()->getEntityManager();

            $confirmation = $em->getRepository('Tom32iUserBundle:Confirmation')->findOneBy(array(
                'user' => $user,
                'action' => Confirmation::ACTION_EMAIL
            ));

            if ($confirmation) 
            {
                $em->remove($confirmation);
                $em->flush();
            }

            $this->sendEmailConfirmation($user);

            $this->get('session')->getFlashBag()->add('success', "We just sent you another comfirmation email. Hopefuly you'll get this one ;).");
        }

        return $this->redirect($this->generateUrl('profile_edit'));
    }

    private function sendEmailConfirmation(&$user)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $user->setEmailValid(false);
        $confirmation = new Confirmation($user, Confirmation::ACTION_EMAIL);

        $em->persist($user);
        $em->persist($confirmation);
        $em->flush();

        $mailer = $this->get('tom32i_user_mailer');
        $mailer->sendToUser($user, 'Confirm your email adress', 'confirmation_email', array(
            'confirmation' =>  $confirmation,
            'user' => $user,
        ));
    }
}