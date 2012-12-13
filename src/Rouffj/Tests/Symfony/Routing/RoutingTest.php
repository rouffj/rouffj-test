<?php

namespace Rouffj\Tests\Symfony\Routing;

use Rouffj\Tests\TestCase;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

use Symfony\Component\Routing\Loader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\RequestContext;



class RoutingTest extends TestCase
{
    public function doSetUp()
    {
        // disable caching of routes in app/cache dir
        $this->container->get('router')->setOptions(array('cache_dir' => null));
    }

    public function testHowToCreateSimpleRoute()
    {
        $collection = new RouteCollection();
        $collection->add('routeA', new Route('/routeA', array(
            '_controller' => 'MyClassActingAsRooter',
        )));
        $this->container->get('router')->setCollection($collection);

        $this->assertEquals(array('_controller' => 'MyClassActingAsRooter', '_route' => 'routeA'),
            $this->container->get('router')->match('/routeA')
        );
    }

    public function testHowToPointRouteToMethodWithoutActionSuffix()
    {
        $collection = new RouteCollection();
        $collection->add('blog_show', new Route('/testt', array(
            '_controller' => 'MyClassActingAsRooter',
        )));
        $this->container->get('router')->setCollection($collection);

        //var_dump($this->container->get('router')->match('/testt'));die;
        //$router = $this->container->get('router');
        //$collection = new RouteCollection();
        //$collection->add('route_a', new Route('/test1'), array(), array('_scheme' => 'http'));
        //$collection->add('route_b', new Route('/test2', array(), array('_scheme' => 'https')));
        //$router->setCollection($collection);

        //$crawler = $this->client->request('GET', '/routeA');
        //var_dump($this->container->get('router')->match('/test1'));
        //echo '<pre>'; var_dump($this->client->getContainer()->get('router')->getRouteCollection()); echo '</pre>'; die();
        //var_dump($this->client->getResponse());
    }

    public function testHowToGeneratePathOrUrl()
    {
        // @see http://symfony.com/doc/current/components/routing.html#generate-a-url

        $router = $this->container->get('router');
        $collection = new RouteCollection();
        $collection->add('routeA', new Route('/routeA'), array(), array('_scheme' => 'http'));
        $router->setCollection($collection);

        // ->generate() by default ALWAYS generates a path
        $this->assertEquals('/routeA', $router->generate('routeA'));
        // ->generate() generates ONLY a full URL by passing the third param as true
        $this->assertEquals('http://localhost/routeA', $router->generate('routeA', array(), true));
    }

    public function testHowToForceScheme()
    {
        $router = $this->container->get('router');
        $collection = new RouteCollection();
        $collection->add('routeA', new Route('/routeA'), array(), array('_scheme' => 'http'));
        $collection->add('routeB', new Route('/routeB', array(), array('_scheme' => 'https')));
        $router->setCollection($collection);

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

    public function testHowToLoadRoutesFromSimpleRoutingFile()
    {
        $locator = new FileLocator(array(__DIR__.'/Fixtures'));
        $loader = new Loader\YamlFileLoader($locator);
        $collection = $loader->load('routesA.yml');
        
        $this->assertEquals(2, count($collection));

        try {
            $loader->load('emptyRoutingFile.yml');
            $this->fail();
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals('The file "emptyRoutingFile.yml" must contain a YAML array.', $e->getMessage());
        }

        // A file with any unexpected key in just one route make the entire file unusable, routes from it can't be loaded.
        try {
            $collection = null;
            $loader->load('routesWithUnexpectedKeys.yml');
            $this->fail();
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals('Yaml routing loader does not support given key: "notAvailableKey". Expected one of the (type, resource, prefix, pattern, options, defaults, requirements).', $e->getMessage());
            $this->assertEquals(null, $collection, 'no RouteCollection loaded');
        }

        // A file with just one route without pattern can't be loadable, all routes from it can't be loaded.
        try {
            $collection = null;
            $collection = $loader->load('routesWithoutPattern.yml');
            $this->fail();
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals('You must define a "pattern" for the "route2" route.', $e->getMessage());
            $this->assertEquals(null, $collection, 'no RouteCollection loaded');
        }
    }

    public function testHowToLoadRoutesFromRoutingFileWithResourcesWithoutKernelBooted()
    {
        // You have to create your own FileLocator which will intercept all included resources in config files.
        $locator = new MyFileLocator(array(__DIR__.'/Fixtures'), array('@MyBundle' => __DIR__.'/Fixtures/MyBundle'));
        $loader = new Loader\YamlFileLoader($locator);

        $collection = $loader->load('routesWithKnowResources.yml');
        $this->assertCount(1, $collection);
        $this->assertEquals('/foo', $collection->get('route1')->getPattern());
    }
}

use Symfony\Component\Config\FileLocator as BaseFileLocator;

class MyFileLocator extends BaseFileLocator
{
    private $bundles;

    public function __construct(array $paths, array $bundles)
    {
        $this->bundles = $bundles;
        parent::__construct($paths);
    }

    /**
     * {@inheritdoc}
     */
    public function locate($file, $currentPath = null, $first = true)
    {
        if ('@' === $file[0]) {
            $bundleName = $this->extractBundleName($file);
            $file = strtr($file, array($bundleName => $this->bundles[$bundleName]));

            return $file;
        }

        return parent::locate($file, $currentPath, $first);
    }

    private function extractBundleName($file)
    {
        return '@MyBundle';
    }

}
