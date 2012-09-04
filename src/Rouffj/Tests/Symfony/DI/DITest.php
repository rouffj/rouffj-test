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
    }

    public function testHowToAddServiceInContainer()
    {
        $this->assertEquals('Rouffj\Tests\Symfony\DI\Fixtures\ServiceB', get_class($this->customContainer->get('service_b')));
    }

    public function testHowToInjectCollectionAsParameter()
    {
        $this->assertEquals(array('value_option1', 'key_option2' => 'value_option2'), $this->customContainer->get('service_a')->getOptions());
    }

    public function testHowToInjectDependencyViaConstructor()
    {
        $this->assertEquals('Rouffj\Tests\Symfony\DI\Fixtures\ServiceB', get_class($this->customContainer->get('service_a')->getServiceB()));
    }

    public function testHowToInjectDependencyViaSetter()
    {
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
}
