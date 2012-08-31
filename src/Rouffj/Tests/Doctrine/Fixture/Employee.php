<?php

namespace Rouffj\Tests\Doctrine\Fixture;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class Employee
{
    /**
     * @ORM\Column(type="string")
     */
    private $lastname;

    private $department;
}
