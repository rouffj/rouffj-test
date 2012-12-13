<?php

namespace Rouffj\Tests\PhpUnit\Fixtures;

class ClassA
{
    private $classB = null;

    function __construct()
    {
        $this->classB = new ClassB();
    }

    public function getClassB()
    {
        return $this->classB;
    }
}

class ClassB
{
    public function getFoo()
    {
        return 'foo';
    }
}
