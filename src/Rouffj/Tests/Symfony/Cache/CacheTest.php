<?php

namespace Rouffj\Tests\Symfony\Cache;

use Rouffj\Tests\TestCase;

class CacheTest extends TestCase
{
    public function doSetUp()
    {
    }

    public function testTiti()
    {
        $cli = $this->client;
        $crawler = $cli->request('GET', '/routeB');
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Hello world!")')->count());
    }
}
