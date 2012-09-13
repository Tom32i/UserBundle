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
 * @ORM\Table(name="account")
 * @ORM\Entity(repositoryClass="Tom32i\UserBundle\Entity\UserRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class User implements AdvancedUserInterface, EquatableInterface, \Serializable
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string $username
     *
     * @ORM\Column(name="username", type="string", length=255)
     */
    private $username;

    /**
     * @var string $usernameCanonical
     *
     * @ORM\Column(name="username_canonical", type="string", length=255, unique=true)
     */
    private $usernameCanonical;

    /**
     * @var string $email
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @var string $emailCanonical
     *
     * @ORM\Column(name="email_canonical", type="string", length=255, unique=true, nullable=true)
     */
    private $emailCanonical;

    /**
     * @var boolean $enabled
     *
     * @ORM\Column(name="enabled", type="boolean")
     */
    private $enabled = false;

    /**
     * @var boolean $expired
     *
     * @ORM\Column(name="expired", type="boolean")
     */
    private $expired = false;

    /**
     * @var boolean $locked
     *
     * @ORM\Column(name="locked", type="boolean")
     */
    private $locked = false;

    /**
     * @var boolean $credentialsExpired
     *
     * @ORM\Column(name="credentials_expired", type="boolean")
     */
    private $credentialsExpired = false;

    /**
     * @var boolean $emailValid
     *
     * @ORM\Column(name="emailValid", type="boolean")
     */
    private $emailValid = false;

    /**
     * @var string $salt
     *
     * @ORM\Column(name="salt", type="string", length=255)
     */
    private $salt;

    /**
     * @var string $password
     *
     * @ORM\Column(name="password", type="string", length=255, nullable=true)
     */
    private $password;

    /**
     * @var array $roles
     *
     * @ORM\Column(name="roles", type="array")
     */
    private $roles;

    /**
     * @var string $fullname
     *
     * @ORM\Column(name="fullname", type="string", length=255, nullable=true)
     */
    private $fullname;

    /**
     * @var string $occupation
     *
     * @ORM\Column(name="occupation", type="string", length=255, nullable=true)
     */
    private $occupation;

    /**
     * @var string $website
     *
     * @ORM\Column(name="website", type="string", length=255, nullable=true)
     */
    private $website;

    /**
     * @var boolean $displayFullname
     *
     * @ORM\Column(name="display_fullname", type="boolean")
     */
    private $displayFullname = false;

    /**
     * @var string $about
     *
     * @ORM\Column(name="about", type="text", nullable=true)
     */
    private $about;

    /**
     * @var string $location
     *
     * @ORM\Column(name="location", type="string", length=255, nullable=true)
     */
    private $location;

    /**
     * @var boolean $tutorial
     *
     * @ORM\Column(name="tutorial", type="boolean")
     */
    private $tutorial = true;

    /**
     * @var \DateTime $lastActivity
     *
     * @ORM\Column(name="last_activity", type="datetime")
     */
    private $lastActivity;

    /**
     * @var \DateTime $register
     *
     * @ORM\Column(name="register", type="datetime")
     */
    private $register;

    /**
     * @var integer $twitterUserId
     *
     * @ORM\Column(name="twitter_user_id", type="integer", unique=true, nullable=true)
     */
    private $twitterUserId;

    /**
     * @var string $twitterScreenName
     *
     * @ORM\Column(name="twitter_screen_name", type="string", length=255, nullable=true)
     */
    private $twitterScreenName;

    /**
     * @var array $twitterToken
     *
     * @ORM\Column(name="twitter_token", type="array", length=255, nullable=true)
     */
    private $twitterToken;

    /**
     * @ORM\ManyToMany(targetEntity="User", inversedBy="leaders")
     * @ORM\JoinTable(name="users_users",
     *      joinColumns={@ORM\JoinColumn(name="leader_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="follower_id", referencedColumnName="id")}
     * )
     **/
    private $followers;
    
    /**
     * @ORM\ManyToMany(targetEntity="User", mappedBy="followers")
     **/
    private $leaders;
    
    /**
     * @ORM\OneToMany(targetEntity="\Tom32i\SiteBundle\Entity\Board", mappedBy="author", cascade={"persist", "remove"})
     * @ORM\OrderBy({"position" = "ASC"})
     */
    private $boards;
    
    /**
     * @ORM\OneToOne(targetEntity="\Tom32i\SiteBundle\Entity\Picture", mappedBy="user", cascade={"persist", "remove"}, orphanRemoval=true)
     **/
    private $image;

    public $currentPassword;
    public $plainPassword;

    public $favBoard;
    public $tools;
    public $resources;
    public $createdNotifications;
    public $notifications;
    public $validations;

    /* META */

    public function __construct()
    {
        $this->salt = md5(uniqid(null, true));
        $this->roles = array('ROLE_USER');
        $this->lastActivity = new \DateTime();
        $this->register = new \DateTime();

        $this->followers = new \Doctrine\Common\Collections\ArrayCollection();
        $this->leaders = new \Doctrine\Common\Collections\ArrayCollection();
        $this->resources = new \Doctrine\Common\Collections\ArrayCollection();
        $this->boards = new \Doctrine\Common\Collections\ArrayCollection();
        $this->notifications = new \Doctrine\Common\Collections\ArrayCollection();
        $this->tools = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function __toString()
    {
        return (string) $this->getUsername();
    }

    /* EVENTS */

    /**
    *   @ORM\PostLoad
    **/
    public function filterBoards()
    {
        foreach($this->boards as $board)
        {
            if($board->isSpecial())
            {
                $this->fav_board = $board;
                $this->boards->removeElement($board);
                return true;
            }
        }
    }

    /**
    *   @ORM\PrePersist
    **/
    public function init()
    {
        $fav = new \Tom32i\SiteBundle\Entity\Board();
        $fav->setTitle('Favorites');
        $fav->setDescription('My favorites');
        $fav->setSpecial(true);
        $fav->setAuthor($this);

        $this->fav_board = $fav;
        $this->boards[] = $fav;
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
        $fields = array(
            'fullname'  => $data->name,
            'website'   => $data->url,
            'about'     => $data->description,
            'location'  => $data->location,
        );

        foreach ($fields as $key => $value) 
        {
            if(empty($this->{$key}) && !empty($value))
            {
                $this->{$key} = $value;
            }
        }
    }

    public function setImageTwitter($data)
    {
        if(empty($this->image) && !empty($data->profile_image_url))
        {
            $img = new \Tom32i\SiteBundle\Entity\Picture();
            $img->setUser($this);
            $success = $img->setFromUrl($data->profile_image_url, 'users/'.$this->id);

            if($success)
            {
                $this->image = $img;
            }
        }
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

    public function name()
    {
        return $this->displayFullname && !empty($this->fullname) ? $this->fullname : $this->username;
    }

    /**
     * Has resource ?
     *
     * @param Tom32i\SiteBundle\Entity\Resource $resource
     */
    public function hasBoardResource(\Tom32i\SiteBundle\Entity\Resource $resource)
    {
        foreach($this->boards->toArray() as $board)
        {
            foreach($board->getResources()->toArray() as $asBoardResource)
            {
                $test = $asBoardResource->getResource();
                if($test == $resource)
                {
                    return true;
                }  
            }
        }
        
        return false;
    }
    
    /**
     * Has favorited ?
     *
     * @param Tom32i\SiteBundle\Entity\Resource $resource
     */
    public function hasFavorite(\Tom32i\SiteBundle\Entity\Resource $resource)
    {   
        $fav = $this->getFavBoard();

        foreach($fav->getResources()->toArray() as $asBoardResource)
        {
            $test = $asBoardResource->getResource();
            if($test == $resource)
            {
                return true;
            }  
        }

        return false;
    }

    /**
     * Is following ?
     *
     * @param Tom32i\UserBundle\Entity\User $user
     */
    public function isFollowing($user)
    {
        
        $followers = $user->getFollowers();
        return $followers->contains($this);
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
     * Set fullname
     *
     * @param string $fullname
     * @return User
     */
    public function setFullname($fullname)
    {
        $this->fullname = $fullname;
    
        return $this;
    }

    /**
     * Get fullname
     *
     * @return string 
     */
    public function getFullname()
    {
        return $this->fullname;
    }

    /**
     * Set occupation
     *
     * @param string $occupation
     * @return User
     */
    public function setOccupation($occupation)
    {
        $this->occupation = $occupation;
    
        return $this;
    }

    /**
     * Get occupation
     *
     * @return string 
     */
    public function getOccupation()
    {
        return $this->occupation;
    }

    /**
     * Set website
     *
     * @param string $website
     * @return User
     */
    public function setWebsite($website)
    {
        $this->website = $website;
    
        return $this;
    }

    /**
     * Get website
     *
     * @return string 
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * Set displayFullname
     *
     * @param boolean $displayFullname
     * @return User
     */
    public function setDisplayFullname($displayFullname)
    {
        $this->displayFullname = $displayFullname;
    
        return $this;
    }

    /**
     * Get displayFullname
     *
     * @return boolean 
     */
    public function getDisplayFullname()
    {
        return $this->displayFullname;
    }

    /**
     * Set about
     *
     * @param string $about
     * @return User
     */
    public function setAbout($about)
    {
        $this->about = $about;
    
        return $this;
    }

    /**
     * Get about
     *
     * @return string 
     */
    public function getAbout()
    {
        return $this->about;
    }

    /**
     * Set location
     *
     * @param string $location
     * @return User
     */
    public function setLocation($location)
    {
        $this->location = $location;
    
        return $this;
    }

    /**
     * Get location
     *
     * @return string 
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set tutorial
     *
     * @param boolean $tutorial
     * @return User
     */
    public function setTutorial($tutorial)
    {
        $this->tutorial = $tutorial;
    
        return $this;
    }

    /**
     * Get tutorial
     *
     * @return boolean 
     */
    public function getTutorial()
    {
        return $this->tutorial;
    }

    /**
     * Set lastActivity
     *
     * @param \DateTime $lastActivity
     * @return User
     */
    public function setLastActivity($lastActivity)
    {
        $this->lastActivity = $lastActivity;
    
        return $this;
    }

    /**
     * Get lastActivity
     *
     * @return \DateTime 
     */
    public function getLastActivity()
    {
        return $this->lastActivity;
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
     * Add followers
     *
     * @param Tom32i\UserBundle\Entity\User $followers
     * @return User
     */
    public function addFollower(\Tom32i\UserBundle\Entity\User $followers)
    {
        $this->followers[] = $followers;
    
        return $this;
    }

    /**
     * Remove followers
     *
     * @param Tom32i\UserBundle\Entity\User $followers
     */
    public function removeFollower(\Tom32i\UserBundle\Entity\User $followers)
    {
        $this->followers->removeElement($followers);
    }

    /**
     * Get followers
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getFollowers()
    {
        return $this->followers;
    }

    /**
     * Add leaders
     *
     * @param Tom32i\UserBundle\Entity\User $leaders
     * @return User
     */
    public function addLeader(\Tom32i\UserBundle\Entity\User $leaders)
    {
        $this->leaders[] = $leaders;
    
        return $this;
    }

    /**
     * Remove leaders
     *
     * @param Tom32i\UserBundle\Entity\User $leaders
     */
    public function removeLeader(\Tom32i\UserBundle\Entity\User $leaders)
    {
        $this->leaders->removeElement($leaders);
    }

    /**
     * Get leaders
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getLeaders()
    {
        return $this->leaders;
    }

    /**
     * Add boards
     *
     * @param Tom32i\SiteBundle\Entity\Board $boards
     * @return User
     */
    public function addBoard(\Tom32i\SiteBundle\Entity\Board $boards)
    {
        $this->boards[] = $boards;
    
        return $this;
    }

    /**
     * Remove boards
     *
     * @param Tom32i\SiteBundle\Entity\Board $boards
     */
    public function removeBoard(\Tom32i\SiteBundle\Entity\Board $boards)
    {
        $this->boards->removeElement($boards);
    }

    /**
     * Get boards
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getBoards()
    {
        return $this->boards;
    }

    /**
     * Set image
     *
     * @param Tom32i\SiteBundle\Entity\Picture $image
     * @return User
     */
    public function setImage(\Tom32i\SiteBundle\Entity\Picture $image = null)
    {
        $this->image = $image;
    
        return $this;
    }

    /**
     * Get image
     *
     * @return Tom32i\SiteBundle\Entity\Picture 
     */
    public function getImage()
    {
        return $this->image;
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
     * Add resources
     *
     * @param Tom32i\SiteBundle\Entity\Resource $resources
     */
    public function addResource(\Tom32i\SiteBundle\Entity\Resource $resources)
    {
        $this->resources[] = $resources;
    }

    /**
     * Get resources
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getResources()
    {
        return $this->resources;
    }

    /**
     * Remove resources
     *
     * @param Tom32i\SiteBundle\Entity\Resource $resources
     */
    public function removeResource(\Tom32i\SiteBundle\Entity\Resource $resources)
    {
        $this->resources->removeElement($resources);
    }

    /**
     * Get favorite board
     *
     * @return Tom32i\SiteBundle\Entity\Board $board
     */
    public function getFavBoard()
    {
        return $this->fav_board;
    }

    /**
     * Add notification
     *
     * @param Tom32i\SiteBundle\Entity\Notification $notification
     */
    public function addNotification(\Tom32i\SiteBundle\Entity\Notification $notification)
    {
        $this->notifications[] = $notification;
    }

    /**
     * Get notifications
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getNotifications()
    {
        return $this->notifications;
    }

    /**
     * Remove notification
     *
     * @param Tom32i\SiteBundle\Entity\Notification $notification
     */
    public function removeNotification(\Tom32i\SiteBundle\Entity\Notification $notification)
    {
        $this->notifications->removeElement($notification);
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
}