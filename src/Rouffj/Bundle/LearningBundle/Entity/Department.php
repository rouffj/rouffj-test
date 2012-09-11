<?php

namespace Rouffj\Bundle\LearningBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class Department
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    public $id;

    /**
     * @ORM\OneToMany(targetEntity="Employee", mappedBy="department")
     */
    private $employees;

    /**
     * @ORM\Column(type="string")
     */
    private $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function addEmployee(Employee $employee)
    {
        $employee->setDepartement($this);
        $this->employees[] = $employee;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * Gets the value of employees
     *
     * @return Employee[]
     */
    public function getEmployees()
    {
        return $this->employees;
    }
}
