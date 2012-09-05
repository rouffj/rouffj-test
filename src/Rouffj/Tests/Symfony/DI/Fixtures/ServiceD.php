<?php

namespace Rouffj\Tests\Symfony\DI\Fixtures;
/**
 * @author Joseph Rouff <rouffj@gmail.com>
 */
class ServiceD
{
    public $varA;
    public $varB;

    function __construct()
    {
    }

    public function getResult()
    {
        $resultA = legacy_functionA();
        $resultB = legacy_functionB();

        return $resultA + $resultB;
    }

    static public function configure(ServiceD $instance)
    {
        $instance->initService();
    }

    public function initService()
    {
        $this->varA = 1;
        $this->varB = 2;
    }
}
