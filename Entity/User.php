<?php

namespace Tom32i\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
 * Tom32i\UserBundle\Entity\User
 *
 * @ORM\MappedSuperclass
 */
abstract class User implements AdvancedUserInterface, EquatableInterface, \Serializable
{
    /**
     * @var string $username
     *
     * @ORM\Column(name="username", type="string", length=255)
     */
    protected $username;

    /**
     * @var string $usernameCanonical
     *
     * @ORM\Column(name="username_canonical", type="string", length=255, unique=true)
     */
    protected $usernameCanonical;

    /**
     * @var string $email
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     */
    protected $email;

    /**
     * @var string $emailCanonical
     *
     * @ORM\Column(name="email_canonical", type="string", length=255, unique=true, nullable=true)
     */
    protected $emailCanonical;

    /**
     * @var boolean $enabled
     *
     * @ORM\Column(name="enabled", type="boolean")
     */
    protected $enabled = false;

    /**
     * @var boolean $expired
     *
     * @ORM\Column(name="expired", type="boolean")
     */
    protected $expired = false;

    /**
     * @var boolean $locked
     *
     * @ORM\Column(name="locked", type="boolean")
     */
    protected $locked = false;

    /**
     * @var boolean $credentialsExpired
     *
     * @ORM\Column(name="credentials_expired", type="boolean")
     */
    protected $credentialsExpired = false;

    /**
     * @var boolean $emailValid
     *
     * @ORM\Column(name="emailValid", type="boolean")
     */
    protected $emailValid = false;

    /**
     * @var string $salt
     *
     * @ORM\Column(name="salt", type="string", length=255)
     */
    protected $salt;

    /**
     * @var string $password
     *
     * @ORM\Column(name="password", type="string", length=255, nullable=true)
     */
    protected $password;

    /**
     * @var array $roles
     *
     * @ORM\Column(name="roles", type="array")
     */
    protected $roles;

    /**
     * @var \DateTime $register
     *
     * @ORM\Column(name="register", type="datetime")
     */
    protected $register;

    /**
     * @var integer $twitterUserId
     *
     * @ORM\Column(name="twitter_user_id", type="integer", unique=true, nullable=true)
     */
    protected $twitterUserId;

    /**
     * @var string $twitterScreenName
     *
     * @ORM\Column(name="twitter_screen_name", type="string", length=255, nullable=true)
     */
    protected $twitterScreenName;

    /**
     * @var array $twitterToken
     *
     * @ORM\Column(name="twitter_token", type="array", length=255, nullable=true)
     */
    protected $twitterToken;

    /**
     * Random string sent to the user email address in order to verify it
     *
     * @var string $confirmationToken
     *
     * @ORM\Column(name="confirmation_token", type="array", length=255)
     */
    protected $confirmationToken;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="password_requested_at", type="datetime", nullable=true)
     */
    protected $passwordRequestedAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="email_requested_at", type="datetime", nullable=true)
     */
    protected $emailRequestedAt;

    public $currentPassword;
    public $plainPassword;

    /* META */

    public function __construct()
    {
        $this->salt = md5(uniqid(null, true));
        $this->confirmationToken = md5(uniqid(null, true));
        $this->roles = array('ROLE_USER');
        $this->register = new \DateTime();
    }

    public function __toString()
    {
        return (string) $this->getUsername();
    }

    /* UTILS */

    public function updateCanonicalFields()
    {
        $this->usernameCanonical = mb_convert_case($this->username, MB_CASE_LOWER, mb_detect_encoding($this->username));
        
        if(!empty($this->email))
        {
            $this->emailCanonical = mb_convert_case($this->email, MB_CASE_LOWER, mb_detect_encoding($this->email));
        }
    }

    public function updatePassword($factory)
    {   
        $valid = true;

        if(!empty($this->plainPassword))
        {
            $encoder = $factory->getEncoder($this);
            $valid = empty($this->password) ? true : $encoder->isPasswordValid($this->password, $this->currentPassword, $this->salt);

            if($valid)
            {
                $this->password = $encoder->encodePassword($this->plainPassword, $this->salt);
            }
        }

        $this->plainPassword = null;

        return $valid;
    }

    /**
     * Set twitter data
     *
     * @param array $access_token
     */
    public function fillFromTwitterToken($access_token)
    {
        $this->twitterUserId = $access_token['user_id'];
        $this->twitterScreenName = $access_token['screen_name'];
        $this->twitterToken = array( 
            'oauth_token' => $access_token['oauth_token'],
            'oauth_token_secret' => $access_token['oauth_token'],
        );
    }

    /**
     * Reset twitter data
     */
    public function resetTwitter()
    {
        $this->twitterUserId = null;
        $this->twitterScreenName = null;
        $this->twitteToken = array();
    }

    public function fillFromTwitter($data)
    {
        
    }

    public function setImageTwitter($data)
    {
        
    }

    /**
     * Serializes the user.
     *
     * The serialized data have to contain the fields used by the equals method and the username.
     *
     * @return string
     */
    public function serialize()
    {
        return serialize(array(
            $this->password,
            $this->salt,
            $this->usernameCanonical,
            $this->username,
            $this->expired,
            $this->locked,
            $this->credentialsExpired,
            $this->enabled,
            $this->id,
        ));
    }

    /**
     * Unserializes the user.
     *
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        $data = unserialize($serialized);
        // add a few extra elements in the array to ensure that we have enough keys when unserializing
        // older data which does not include all properties.
        $data = array_merge($data, array_fill(0, 2, null));

        list(
            $this->password,
            $this->salt,
            $this->usernameCanonical,
            $this->username,
            $this->expired,
            $this->locked,
            $this->credentialsExpired,
            $this->enabled,
            $this->id
        ) = $data;
    }

    public function eraseCredentials()
    {
        $this->plainPassword = null;
    }

    public function isEqualTo(UserInterface $user)
    {
        return $this->usernameCanonical === $user->getUsernameCanonical();
    }

    /**
     * Checks whether the user's account has expired.
     *
     * Implements AdvancedUserInterface
     *
     * @return Boolean true if the user's account is non expired, false otherwise
     */
    public function isAccountNonExpired()
    {
        return $this->expired == false;

        /*if (true === $this->expired) {
            return false;
        }

        if (null !== $this->expiresAt && $this->expiresAt->getTimestamp() < time()) {
            return false;
        }

        return true;*/
    }

    public function isAccountNonLocked()
    {
        return $this->locked == false;
    }

    /**
     * Checks whether the user's credentials (password) has expired.
     *
     * Implements AdvancedUserInterface
     *
     * @return Boolean true if the user's credentials are non expired, false otherwise
     */
    public function isCredentialsNonExpired()
    {
        return $this->credentialsExpired == false;

        /*if (true === $this->credentialsExpired) {
            return false;
        }

        if (null !== $this->credentialsExpireAt && $this->credentialsExpireAt->getTimestamp() < time()) {
            return false;
        }

        return true;*/
    }

    public function isValid()
    {
        return $this->isEnabled() && $this->isAccountNonExpired() && $this->isAccountNonLocked();
    }

    /* GETTER / SETTERS */

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
     * Set username
     *
     * @param string $username
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;
    
        return $this;
    }

    /**
     * Get username
     *
     * @return string 
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set usernameCanonical
     *
     * @param string $usernameCanonical
     * @return User
     */
    public function setUsernameCanonical($usernameCanonical)
    {
        $this->usernameCanonical = $usernameCanonical;
    
        return $this;
    }

    /**
     * Get usernameCanonical
     *
     * @return string 
     */
    public function getUsernameCanonical()
    {
        return $this->usernameCanonical;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;
    
        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set emailCanonical
     *
     * @param string $emailCanonical
     * @return User
     */
    public function setEmailCanonical($emailCanonical)
    {
        $this->emailCanonical = $emailCanonical;
    
        return $this;
    }

    /**
     * Get emailCanonical
     *
     * @return string 
     */
    public function getEmailCanonical()
    {
        return $this->emailCanonical;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     * @return User
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    
        return $this;
    }

    /**
     * Get enabled
     *
     * @return boolean 
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set salt
     *
     * @param string $salt
     * @return User
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;
    
        return $this;
    }

    /**
     * Get salt
     *
     * @return string 
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;
    
        return $this;
    }

    /**
     * Get password
     *
     * @return string 
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Sets the roles of the user.
     *
     * This overwrites any previous roles.
     *
     * @param array $roles
     *
     * @return User
     */
    public function setRoles(array $roles)
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * Adds a role to the user.
     *
     * @param string $role
     *
     * @return User
     */
    public function addRole($role)
    {
        $role = strtoupper($role);

        if (!in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    /**
     * Returns the user roles
     *
     * @return array The roles
     */
    public function getRoles()
    {
        return $this->roles;
    }

     /**
     * Removes a role to the user.
     *
     * @param string $role
     */
    public function removeRole($role)
    {
        if (false !== $key = array_search(strtoupper($role), $this->roles, true)) {
            unset($this->roles[$key]);
            $this->roles = array_values($this->roles);
        }
    }

    /**
     * Set register
     *
     * @param \DateTime $register
     * @return User
     */
    public function setRegister($register)
    {
        $this->register = $register;
    
        return $this;
    }

    /**
     * Get register
     *
     * @return \DateTime 
     */
    public function getRegister()
    {
        return $this->register;
    }

    /**
     * Set twitterUserId
     *
     * @param integer $twitterUserId
     * @return User
     */
    public function setTwitterUserId($twitterUserId)
    {
        $this->twitterUserId = $twitterUserId;
    
        return $this;
    }

    /**
     * Get twitterUserId
     *
     * @return integer 
     */
    public function getTwitterUserId()
    {
        return $this->twitterUserId;
    }

    /**
     * Set twitterScreenName
     *
     * @param string $twitterScreenName
     * @return User
     */
    public function setTwitterScreenName($twitterScreenName)
    {
        $this->twitterScreenName = $twitterScreenName;
    
        return $this;
    }

    /**
     * Get twitterScreenName
     *
     * @return string 
     */
    public function getTwitterScreenName()
    {
        return $this->twitterScreenName;
    }

    /**
     * Set twitterToken
     *
     * @param array $twitterToken
     * @return User
     */
    public function setTwitterToken($twitterToken)
    {
        $this->twitterToken = $twitterToken;
    
        return $this;
    }

    /**
     * Get twitterToken
     *
     * @return array 
     */
    public function getTwitterToken()
    {
        return $this->twitterToken;
    }

    /**
     * Get enabled
     *
     * @return boolean 
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set expired
     *
     * @param boolean $expired
     * @return User
     */
    public function setExpired($expired)
    {
        $this->expired = $expired;
    
        return $this;
    }

    /**
     * Get expired
     *
     * @return boolean 
     */
    public function getExpired()
    {
        return $this->expired;
    }

    /**
     * Set locked
     *
     * @param boolean $locked
     * @return User
     */
    public function setLocked($locked)
    {
        $this->locked = $locked;
    
        return $this;
    }

    /**
     * Get locked
     *
     * @return boolean 
     */
    public function getLocked()
    {
        return $this->locked;
    }

    /**
     * Set credentialsExpired
     *
     * @param boolean $credentialsExpired
     * @return User
     */
    public function setCredentialsExpired($credentialsExpired)
    {
        $this->credentialsExpired = $credentialsExpired;
    
        return $this;
    }

    /**
     * Get credentialsExpired
     *
     * @return boolean 
     */
    public function getCredentialsExpired()
    {
        return $this->credentialsExpired;
    }

    /**
     * Sets the plain password.
     *
     * @param string $password
     *
     * @return User
     */
    public function setPlainPassword($password)
    {
        $this->plainPassword = $password;

        return $this;
    }

    /**
     * Set emailValid
     *
     * @param boolean $emailValid
     * @return User
     */
    public function setEmailValid($emailValid)
    {
        $this->emailValid = $emailValid;
    
        return $this;
    }

    /**
     * Get emailValid
     *
     * @return boolean 
     */
    public function isEmailValid()
    {
        return $this->emailValid;
    }

    /**
     * Reset Confirmation Token
     */
    public function resetConfirmationToken()
    {
        $this->confirmationToken = md5(uniqid(null, true));
    }

    /**
     * Is Confirmation Password Valid
     *
     * @return boolean
     */
    public function isConfirmationPasswordValid()
    {
        if($this->passwordRequestedAt == null)
        {
            return false;
        }

        $diff = $this->passwordRequestedAt->diff( new \DateTime() );

        return $diff->i <= 15;
    }

    /**
     * Is Confirmation Email Valid
     *
     * @return boolean
     */
    public function isConfirmationEmailValid()
    {
        if($this->emailRequestedAt == null)
        {
            return false;
        }

        $diff = $this->emailpasswordRequestedAt->diff( new \DateTime() );

        return $diff->h <= 1;
    }

    /**
     * Reset Password
     *
     * @return boolean
     */
    public function resetPassword()
    {
        $this->resetConfirmationToken();
        $this->passwordRequestedAt = new \DateTime();
    }

    /**
     * Reset Email
     *
     * @return boolean
     */
    public function resetEmail()
    {
        $this->resetConfirmationToken();
        $this->emailRequestedAt = new \DateTime();
        $this->emailValid = false;
    }

    public function onProfileEdit()
    {
        
    }
}