<?php

namespace Tom32i\UserBundle\Services;

use Symfony\Component\Templating\EngineInterface;
use Tom32i\UserBundle\Entity\User;

class Mailer
{
	protected $mailer;
    protected $templating;
    protected $sitename;
    protected $email;

    public function __construct(\Swift_Mailer $mailer, EngineInterface $templating, $sitename, $email)
    {
        $this->mailer = $mailer;
        $this->templating = $templating;
        $this->sitename = $sitename;
        $this->email = $email;
    }

    public function sendToUser(User $user, $title, $template, $params = array())
    {
        if(!array_key_exists('title', $params))
        {
            $params['title'] = $title;
        }
        if(!array_key_exists('user', $params))
        {
            $params['user'] = $user;
        }

    	$content = $this->templating->render('Tom32iUserBundle:Email:' . $template . '.html.twig', $params);

        $message = \Swift_Message::newInstance()
            ->setSubject($this->sitename . ' | ' . $title)
            ->setFrom($this->email)
            ->setTo(array($user->getEmail() => $user->getUsername()))
            ->setBody($content, 'text/html')
        ;

        $this->mailer->send($message);
    }
}