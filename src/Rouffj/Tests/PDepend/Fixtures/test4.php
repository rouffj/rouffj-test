<?php

namespace Test;

class ClassA
{
    public $dep1 = array();
    public $dep2 = 4;

    public function __construct()
    {
        $test = 1;
        $tt = 'ssss';
        $this->dep1 = new \Test\ClassB();
        $this->dep2 = new stdClass();
        $this->dep1->test();
    }
}

class ClassB
{
    function test()
    {
        //return $this;
    }
}

