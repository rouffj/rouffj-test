<?php

namespace Rouffj\Tests\Symfony\Form;

use Rouffj\Tests\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;

use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class DITest extends TestCase
{
    public function doSetUp()
    {
        $this->customContainer = new ContainerBuilder();
        $xmlLoader = new XmlFileLoader($this->customContainer, new FileLocator(array(__DIR__.'/Fixtures')));
        $xmlLoader->load('services.xml');
        $this->customContainer->setParameter('kernel.root_dir', $this->container->getParameter('kernel.root_dir'));
    }

    public function testHowToAddServiceInContainer()
    {
        $this->assertEquals('Rouffj\Tests\Symfony\DI\Fixtures\ServiceB', get_class($this->customContainer->get('service_b')));
    }

    public function testHowToInjectCollectionAsParameter()
    {
        $this->assertEquals(array('value_option1', 'key_option2' => 'value_option2'), $this->customContainer->get('service_a')->getOptions());
    }

    /**
     * Use the injection of services via constructor only for REQUIRED services.
     *
     * @link http://symfony.com/doc/current/components/dependency_injection/types.html#constructor-injection
     */
    public function testHowToInjectDependencyViaConstructor()
    {
        // The service named serviceD is injected via constructor, check the services.xml
        $this->assertEquals('Rouffj\Tests\Symfony\DI\Fixtures\ServiceB', get_class($this->customContainer->get('service_a')->getServiceB()));
    }

    /**
     * Use the injection of services via setter only for services NOT VITAL for service creation.
     *
     * @link http://symfony.com/doc/current/components/dependency_injection/types.html#setter-injection
     */
    public function testHowToInjectDependencyViaSetter()
    {
        // The service named serviceD is injected via setter, check the services.xml
        $this->assertEquals('Rouffj\Tests\Symfony\DI\Fixtures\ServiceD', get_class($this->customContainer->get('service_a')->getServiceD()));
    }

    /**
     * This attribute avoid that an exception is thrown.
     * Usecases:
     *  - When injecting services like Logger.
     */
    public function testHowToInjectOptionalDependency()
    {
        $this->assertEquals(null, $this->customContainer->get('service_a')->getServiceC(),
            'serviceC is an optional dependency as on-invalid="ignore" is defined, so it should return null'
        );
    }

    /**
     * This can be usefull to reuse legacy system.
     */
    public function testHowToRequireFileForBoostrappingService()
    {
        $this->assertEquals(10, $this->customContainer->get('service_d')->getResult());
        $this->assertEquals(10, legacy_functionA() + legacy_functionB(), 'after a service requires a files with regular functions, they becomes accessible globally :(');
    }
}
