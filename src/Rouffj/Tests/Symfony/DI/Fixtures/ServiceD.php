<?php

namespace Rouffj\Tests\Symfony\DI\Fixtures;
/**
 * @author Joseph Rouff <rouffj@gmail.com>
 */
class ServiceD
{
    function __construct()
    {
    }

    public function getResult()
    {
        $resultA = legacy_functionA();
        $resultB = legacy_functionB();

        return $resultA + $resultB;
    }
}
