<?php
namespace ArhframeUser\entities;
use Doctrine\Common\Collections\ArrayCollection;
/**
 *
 * @Table(name="arhframe_users")
 * @Entity
 */
class User implements \Serializable
{
    /**
     * @Column(type="integer")
     * @Id
     * @GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @Column(type="string", length=25, unique=true)
     */
    private $username;

    /**
     * @Column(type="string", length=64)
     */
    private $password;

    /**
     * @Column(type="string", length=60, unique=true)
     */
    private $email;

    /**
     * @Column(name="is_active", type="boolean")
     */
    private $isActive;

    /**
     * @ManyToMany(targetEntity="Role", inversedBy="users")
     *
     */
    private $roles;


    public function __construct()
    {
        $this->isActive = true;
        $this->roles = new ArrayCollection();
    }

    /**
     * @inheritDoc
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @inheritDoc
     */
    public function getSalt()
    {
        // you *may* need a real salt depending on your encoder
        // see section on salt below
        return null;
    }

    public function getRoles()
    {
    	return $this->roles->toArray();
    }
    public function addRole(Role $role){
    	if($this->roles->contains($roles)){
    		return;
    	}
    	$this->roles->add($role);
    	$role->addUser($this);
    }
    /**
     * @inheritDoc
     */
    public function getPassword()
    {
        return $this->password;
    }


    /**
     * @inheritDoc
     */
    public function eraseCredentials()
    {
    }

    /**
     * @see \Serializable::serialize()
     */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->username,
            $this->password,
            // see section on salt below
            // $this->salt,
        ));
    }

    /**
     * @see \Serializable::unserialize()
     */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->username,
            $this->password,
            // see section on salt below
            // $this->salt
        ) = unserialize($serialized);
    }
	public function setUsername($username) {
		$this->username = $username;
		return $this;
	}
	public function setPassword($password) {
		$this->password = $password;
		return $this;
	}
	public function getEmail() {
		return $this->email;
	}
	public function setEmail($email) {
		$this->email = $email;
		return $this;
	}
	public function getIsActive() {
		return $this->isActive;
	}
	public function setIsActive($isActive) {
		$this->isActive = $isActive;
		return $this;
	}
	public function setRoles($roles) {
		$this->roles = $roles;
		return $this;
	}
	public function getId() {
		return $this->id;
	}
	public function setId($id) {
		$this->id = $id;
		return $this;
	}



}