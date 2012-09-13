<?php

namespace Tom32i\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Tom32i\UserBundle\Entity\Confirmation
 *
 * @ORM\Table(name="confirmation", indexes={@ORM\Index(name="token", columns={"token"})})
 * @ORM\Entity
 */
class Confirmation
{
    const ACTION_EMAIL = 0;
    const ACTION_PASSWORD = 1;

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime $created
     *
     * @ORM\Column(name="created", type="datetime")
     */
    private $created;

    /**
     * @var string $token
     *
     * @ORM\Column(name="token", type="string", length=255)
     */
    private $token;

    /**
     * @var integer $action
     *
     * @ORM\Column(name="action", type="smallint")
     */
    private $action = 0;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="validations")
     * @ORM\JoinColumn(name="user", referencedColumnName="id")
     */
    private $user;


    public function __construct(\Tom32i\UserBundle\Entity\User $user, $action)
    {
        $this->token = md5(uniqid(null, true));
        $this->created = new \DateTime();
        $this->user = $user;
        $this->setAction($action);
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return ValidationEmail
     */
    public function setCreated($created)
    {
        $this->created = $created;
    
        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime 
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set token
     *
     * @param string $token
     * @return ValidationEmail
     */
    public function setToken($token)
    {
        $this->token = $token;
    
        return $this;
    }

    /**
     * Get token
     *
     * @return string 
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set action
     *
     * @param integer $action
     * @return ValidationEmail
     */
    public function setAction($action)
    {
        $actions = $this->getActions();

        if(in_array($action, $actions))
        {
            $this->action = $action;
        }
    
        return $this;
    }

    /**
     * Get action
     *
     * @return integer 
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set user
     *
     * @param Tom32i\UserBundle\Entity\User $user
     * @return ValidationEmail
     */
    public function setUser(\Tom32i\UserBundle\Entity\User $user = null)
    {
        $this->user = $user;
    
        return $this;
    }

    /**
     * Get user
     *
     * @return Tom32i\UserBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Get user
     *
     * @return boolean
     */
    public function isValid($action)
    {
        if($this->action != $action)
        {
            return false;
        }

        $diff = $this->created->diff( new \DateTime() );

        switch ($action) 
        {
            case self::ACTION_PASSWORD:

                return $diff->i <= 15;
            
            default:

                return $diff->h <= 1;
        }
    }

    private function getActions()
    {
        return array(
            self::ACTION_EMAIL, 
            self::ACTION_PASSWORD
        );
    }
}