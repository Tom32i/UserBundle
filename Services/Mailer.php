<?php

namespace Tom32i\UserBundle\Services;

use Symfony\Component\Templating\EngineInterface;
use Tom32i\UserBundle\Entity\User;

class Mailer
{
	protected $mailer;
    protected $templating;

    public function __construct(\Swift_Mailer $mailer, EngineInterface $templating)
    {
        $this->mailer = $mailer;
        $this->templating = $templating;
    }

    public function sendToUser(User $user, $title, $template, $params)
    {
    	if(!array_key_exists('title', $params))
    	{
    		$params['title'] = $title;
    	}

    	$content = $this->templating->render('Tom32iUserBundle:Email:' . $template . '.html.twig', $params);

        $message = \Swift_Message::newInstance()
            ->setSubject('Keeptools | ' . $title)
            ->setFrom('noreply@keeptools.com')
            ->setTo(array($user->getEmail() => $user->name()))
            ->setBody($content, 'text/html')
        ;

        $this->mailer->send($message);
    }
}