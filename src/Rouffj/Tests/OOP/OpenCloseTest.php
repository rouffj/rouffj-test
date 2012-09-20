<?php

namespace Rouffj\Tests\Symfony\OOP;

use Rouffj\Tests\TestCase;

// We have to require this file because it will contains all classes related to this how to.
// In consequence the autoloading can't work because we don't respect the PSR-0 convention (1 class per file).
// So we require the file by hand.
require_once __DIR__.'/Fixtures/ObjectConverter.php';
use Rouffj\Tests\OOP\Fixtures;

class OpenCloseTest extends TestCase
{
    public function doSetUp()
    {
    }

    /**
     * This can be particularly useful in SOAP/DB context when we have 2 domain models
     * which diverges.
     */
    public function testHowToBuildObjectConverterAvoidingIfs()
    {
        $userFromDb = new Fixtures\DbUser('john', 'Smith from db', 'john.smith');
        $userFromSoap = new Fixtures\SoapUser('john', 'Smith from soap');
        $converters = array(
            new Fixtures\UserConverter(),
        );

        $modelConverter = new Fixtures\ModelConverter($converters);
        $model = $modelConverter->convert($userFromDb);

        $this->assertEquals('Rouffj\Tests\OOP\Fixtures\SoapUser', get_class($model));
        $this->assertEquals('Smith from db', $model->last);
        $model = $modelConverter->convert($userFromSoap);
        $this->assertEquals('Rouffj\Tests\OOP\Fixtures\DbUser', get_class($model));
        $this->assertEquals('Smith from soap', $model->lastname);
        $this->assertEquals('no login', $model->login);
    }
}


