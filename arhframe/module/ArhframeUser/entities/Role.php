<?php
namespace ArhframeUser\entities;

use Doctrine\Common\Collections\ArrayCollection;
/**
 * @Table(name="arhframe_roles")
 * @Entity
 */
class Role
{
	/**
	 * @Column(name="id", type="integer")
	 * @Id()
	 * @GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @Column(name="name", type="string", length=30)
	 */
	private $name;

	/**
	 * @Column(name="role", type="string", length=20, unique=true)
	 */
	private $role;

	/**
	 * @ManyToMany(targetEntity="User", mappedBy="roles")
	 */
	private $users;

	public function __construct()
	{
		$this->users = new ArrayCollection();
	}

	public function getRole()
	{
		return $this->role;
	}
	public function getName() {
		return $this->name;
	}
	public function setName($name) {
		$this->name = $name;
		return $this;
	}
	public function setRole($role) {
		$this->role = $role;
		return $this;
	}
	public function getUsers() {
		return $this->users;
	}
	public function setUsers($users) {
		$this->users = $users;
		return $this;
	}
	public function addUser(User $user){
		if($this->users->contains($user)){
			return;
		}
		$this->users->add($user);
	}
	public function getId() {
		return $this->id;
	}
	public function setId($id) {
		$this->id = $id;
		return $this;
	}
	
}