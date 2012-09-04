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
    }

    public function testHowToUseXmlAsContainerDefinition()
    {
        $xmlLoader = new XmlFileLoader($this->customContainer, new FileLocator(array(__DIR__.'/Fixtures')));
        $xmlLoader->load('services.xml');
        $this->assertEquals('Rouffj\Tests\Symfony\DI\Fixtures\ServiceA', get_class($this->customContainer->get('service_a')));
        $this->assertEquals('Rouffj\Tests\Symfony\DI\Fixtures\ServiceB', get_class($this->customContainer->get('service_a')->getServiceB()));
        $this->assertEquals(null, $this->customContainer->get('service_a')->getServiceC(), 'optional dependency, so it should return null');
        $this->assertEquals('Rouffj\Tests\Symfony\DI\Fixtures\ServiceD', get_class($this->customContainer->get('service_a')->getServiceD()));
        $this->assertEquals(array('value_option1', 'key_option2' => 'value_option2'), $this->customContainer->get('service_a')->getOptions());
    }
}
