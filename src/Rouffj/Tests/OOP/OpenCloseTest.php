<?php

namespace Rouffj\Tests\Symfony\OOP;

use Rouffj\Tests\TestCase;

// We have to require this file because it will contains all classes related to this how to.
// In consequence the autoloading can't work because we don't respect the PSR-0 convention (1 class per file).
// So we require the file by hand.
require_once __DIR__.'/Fixtures/ObjectConverter.php';
use Rouffj\Tests\OOP\Fixtures;

/**
 * To apply correctly the open close principle, you have to ask to your product owner
 * what will be the axes of change
 */
class OpenCloseTest extends TestCase
{
    public function doSetUp()
    {
    }

    /**
     * This can be particularly useful in SOAP/DB context when we have 2 domain models
     * which diverges.
     *
     * Limitations:
     * - This design supposes that the main change axis is ONLY the increasing number of objets
     *   to convert (SoapModel <-> DbModel). The ModelConverter is CLOSED for ALTERATION for
     *   this axe of changes because we have no longer need to alter the ModelConverter but OPENED
     *   for EXTENSION because just creating a new Converter will be handled by ModelConverter because
     *   it iterates over all existing converters.
     * - If we have have to add regularly new model type (Soap, Db, Rest, Github...) with the actual
     *   design we have to alter each converter by adding the new type to supports() method + adding
     *   constant + add new else if. So it is not optimal if we add often new model type but is suffiscient
     *   if it is very exceptional.
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


