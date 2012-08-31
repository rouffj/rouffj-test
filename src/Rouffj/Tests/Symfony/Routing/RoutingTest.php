<?php

namespace Rouffj\Tests\Symfony\Form;

use Rouffj\Tests\TestCase;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

use Symfony\Component\Routing\Loader;
use Symfony\Component\Routing\RequestContext;

class RoutingTest extends TestCase
{
    public function doSetUp()
    {
    }

    public function testHowToCreateSimpleRoute()
    {
        $collection = new RouteCollection();
        $collection->add('blog_show', new Route('/test1', array(
            '_controller' => 'MyClassActingAsRooter',
        )));
        $this->container->get('router')->setCollection($collection);

        var_dump($this->container->get('router')->match('/test'));
        $this->assertEquals(array('_controller' => 'MyClassActingAsRooter', '_route' => 'blog_show'),
            $this->container->get('router')->match('/test1')
        );
    }

    public function testHowToPointRouteToMethodWithoutActionSuffix()
    {
        //$collection = new RouteCollection();
        //$collection->add('blog_show1', new Route('/testt', array(
        //    '_controller' => 'MyClassActingAsRooter',
        //)));
        //$this->container->get('router')->setCollection($collection);

        //var_dump($this->container->get('router')->match('/testt'));die;
        //$router = $this->container->get('router');
        //$collection = new RouteCollection();
        //$collection->add('route_a', new Route('/test1'), array(), array('_scheme' => 'http'));
        //$collection->add('route_b', new Route('/test2', array(), array('_scheme' => 'https')));
        //$router->setCollection($collection);

        //$crawler = $this->client->request('GET', '/routeA');
        var_dump($this->container->get('router')->match('/test1'));
        //echo '<pre>'; var_dump($this->client->getContainer()->get('router')->getRouteCollection()); echo '</pre>'; die();
        //var_dump($this->client->getResponse());
    }

    public function testHowToGeneratePathOrUrl()
    {
        // @see http://symfony.com/doc/current/components/routing.html#generate-a-url

        $router = $this->container->get('router');
        $collection = new RouteCollection();
        $collection->add('routeA', new Route('/routeA'), array(), array('_scheme' => 'http'));
        $collection->add('routeB', new Route('/routeB', array(), array('_scheme' => 'https')));
        $router->setCollection($collection);

        // ->generate() by default ALWAYS generates a path
        $this->assertEquals('/routeA', $router->generate('routeA'));
        // ->generate() generates ONLY a full URL by passing the third param as true
        $this->assertEquals('http://localhost/routeA', $router->generate('routeA', array(), true));

        // WARNING: There is ONLY one case where without passing the third argument the router generate an URL, when the current
        // scheme is different from the target route (http -> https).
        $context = new RequestContext();
        $context->setScheme('http');
        $router->setContext($context);
        $this->assertEquals('https://localhost/routeB', $router->generate('routeB', array()));

        $context = new RequestContext();
        $context->setScheme('https');
        $router->setContext($context);
        $this->assertEquals('http://localhost/routeA', $router->generate('routeA', array()),
            "BUG: This case failed because only _scheme is warmup (in apptestUrlGenerator) only when _scheme is https. This should be the case also for http"
        );
    }
}
