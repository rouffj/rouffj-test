<?php

namespace Rouffj\Tests\Symfony\Cache\Fixtures;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

class FooController
{
    /**
     * @Route("/routeB")
     */
    public function helloAction()
    {
        return new Response('Hello world!');
    }

    /**
     * @Route("/routeC")
     */
    public function hellaAction()
    {
        $date = new \DateTime();
        $date->modify('+5 seconds');

        $response = new Response('');
        $response->setExpires($date);
    }
}
