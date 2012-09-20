<?php

namespace Rouffj\Tests\OOP\Fixtures;

use Symfony\Component\Form\DataTransformerInterface;

interface ConverterInterface
{
    function convert($modelToConvert);
    function supports($modelToConvert);
}

class UserConverter implements ConverterInterface
{
    const SOAP_NAME = 'Rouffj\Tests\OOP\Fixtures\SoapUser';
    const DB_NAME = 'Rouffj\Tests\OOP\Fixtures\DbUser';

    public function convert($modelToConvert)
    {
        if (self::SOAP_NAME === get_class($modelToConvert)) {
            $model = new DbUser($modelToConvert->first, $modelToConvert->last, 'no login');
        } else {
            $model = new SoapUser($modelToConvert->firstname, $modelToConvert->lastname);
        }

        return $model;
    }

    public function supports($modelToConvert)
    {
        return self::SOAP_NAME === get_class($modelToConvert) || self::DB_NAME === get_class($modelToConvert);
    }
}

class ModelConverter
{
    private $converters = array();

    public function __construct(array $converters)
    {
        $this->converters = $converters;
    }

    public function convert($modelToConvert)
    {
        foreach ($this->converters as $converter) {
            if ($converter->supports($modelToConvert)) {
                return $converter->convert($modelToConvert);
            }
        }
    }
}


class DbUser
{
    public $firstname;
    public $lastname;
    public $login;

    public function __construct($firstname, $lastname, $login)
    {
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->login = $login;
    }
}

class SoapUser
{
    public $first;
    public $last;

    public function __construct($firstname, $lastname)
    {
        $this->first = $firstname;
        $this->last = $lastname;
    }
}
