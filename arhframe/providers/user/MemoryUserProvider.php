<?php
import('arhframe.module.ArhframeUser.entities.*');

class MemoryUserProvider
{
    private $users = array();
    private $roles;
    private $idRole = 1;
    private $rolesObject = array();
    private $encoder;

    public function __construct()
    {


    }

    public function loadUsers()
    {
        $id = 1;
        foreach ($this->users as $username => $userArray) {
            $user = new ArhframeUser\entities\User();
            $user->setPassword($this->encoder->crypt($userArray['password']));
            $user->setUsername($username);
            $user->setId($id);
            $user->addRole($this->getRoleObject($userArray['role']));
            $this->addRolesFromHierarichal($user, $userArray['role']);
            $this->users[$username] = $user;
            $id++;

        }
    }

    public function addRolesFromHierarichal(ArhframeUser\entities\User $user, $role)
    {
        if (empty($this->roles[$role])) {
            return;
        }
        if (!is_array($this->roles[$role])) {
            $user->addRole($this->getRoleObject($this->roles[$role]));
            return;
        }
        foreach ($this->roles[$role] as $roleHierarchy) {
            $user->addRole($this->getRoleObject($this->roles[$role]));
        }
    }

    private function getRoleObject($role)
    {
        if (!empty($this->rolesObject[$role])) {
            return $this->rolesObject[$role];
        }
        $roleObject = new \ArhframeUser\entities\Role();
        $roleObject->setId($this->idRole);
        $roleObject->setName($role);
        $roleObject->setRole($role);
        $this->idRole++;
        $this->rolesObject[$role] = $roleObject;
        return $roleObject;
    }

    public function getUser($userName)
    {
        if (empty($this->users[$userName])) {
            throw new ArhframeProviderMemoryException('User with username "' . $userName . '" not found.');
        }
        return $this->users[$userName];
    }

    public function getAllUser()
    {
        return $this->users;
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @param array $roles
     */
    public function setRoles(array $roleHierarchy)
    {
        $this->roles = $roleHierarchy;
    }

    /**
     * @return array
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @param mixed $encoder
     */
    public function setEncoder($encoder)
    {
        $this->encoder = $encoder;
    }

    /**
     * @param array $users
     */
    public function setUsers(array $userInMemory)
    {
        $this->users = $userInMemory;
    }

}