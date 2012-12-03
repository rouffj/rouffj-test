<?php

namespace Rouffj\Tests\Symfony\Cache\Fixtures;

use Symfony\Component\HttpFoundation\Response;

class FooController
{
    public function helloAction()
    {
        return new Response('Hello world!');
    }
}
