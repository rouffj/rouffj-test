<?php

namespace Rouffj\Tests\Symfony\Form;

//require __DIR__.'/Fixtures/embedded_instanciation.php';

use Rouffj\Tests\PhpUnit;
use Rouffj\Tests\TestCase;


class PhpUnitTest extends TestCase
{
    public function doSetUp()
    {
    }

    public function testHowToStubbingHardCodedDependencies()
    {
        $a = new PhpUnit\Fixtures\ClassA();
        $this->assertEquals('foo', $a->getClassB()->getFoo());

        $mock = $this->getMock('Rouffj\Tests\PhpUnit\Fixtures\ClassB');
        $aBis = new PhpUnit\Fixtures\ClassA();
        $this->assertNotEquals(null, $a->getClassB()->getFoo(), 'Just declaring a $mock is not suffisant.');

        $mock = $this->getMock('Rouffj\Tests\PhpUnit\Fixtures\ClassB');
        $mock->staticExpects();
        $aBis = new PhpUnit\Fixtures\ClassA();
        $this->assertEquals(null, $a->getClassB()->getFoo());
    }
}
