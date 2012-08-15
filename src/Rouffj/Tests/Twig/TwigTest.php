<?php

namespace Rouffj\Tests\Twig;

use Rouffj\Tests\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TwigTest extends WebTestCase
{
    private  $container = null;

    public function setUp()
    {
        $this->container = $this->createClient()->getContainer();
    }

    public function testTwig()
    {
        $this->assertEquals(1, 1);
        $this->assertEquals(1, 1);
    }
}
