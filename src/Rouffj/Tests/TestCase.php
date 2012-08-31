<?php

namespace Rouffj\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class TestCase extends WebTestCase
{
    protected  $container = null;
    protected  $client = null;

    public function setUp()
    {
        $this->client = $this->createClient();
        $this->container = $this->client->getContainer();
        $this->doSetup();
    }
}
