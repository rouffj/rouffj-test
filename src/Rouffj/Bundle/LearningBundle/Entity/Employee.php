<?php

namespace Rouffj\Bundle\LearningBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class Employee
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    public $id;

    /**
     * @ORM\Column(type="string")
     */
    private $lastname;


    /**
     * @ORM\ManyToOne(targetEntity="Department", inversedBy="employees")
     */
    private $department;

    public function __construct($lastname)
    {
        $this->lastname = $lastname;
    }

    public function setDepartement(Department $dept)
    {
        $this->department = $dept;
    }
}
