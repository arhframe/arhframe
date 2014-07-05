<?php
namespace test\entities;
/**
 * @Entity 
 * @Table(name="users")
 */
class User
{
    /**
     * @Id 
     * @GeneratedValue 
     * @Column(type="integer")
     * @var int
     */
    protected $id;
    /**
     * @Column(type="string")
     * @var string
     */
    protected $name;

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }
}