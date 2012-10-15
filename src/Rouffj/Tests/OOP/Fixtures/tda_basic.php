<?php

class ClassA
{
    const STATE_A = 1;
    const STATE_B = 2;

    private $state;

    public function __construct()
    {
        $this->state = self::STATE_A;
    }

    public function setObject1()
    {
        // code...
    }

    public function getState()
    {
        return $this->state;
    }
}
