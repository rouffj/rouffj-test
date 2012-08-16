<?php

namespace Rouffj\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class TestCase extends WebTestCase
{
    protected  $container = null;

    public function setUp()
    {
        $this->container = $this->createClient()->getContainer();
        $this->doSetup();
    }
}
