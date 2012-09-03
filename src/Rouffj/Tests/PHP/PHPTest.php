<?php

namespace Rouffj\Tests\Symfony\Form;

use Rouffj\Tests\TestCase;

class PHPTest extends TestCase
{
    public function doSetUp()
    {
    }

    public function testHowToChangeErrorMessagesIntoCatchableException()
    {
        // We are restoring the default PHP error handler, because Symfony defines its own.
        restore_error_handler();

        // 1) PHP Default behavior
        try {
            @file_get_contents('/ThisIsAFileWhichNotExists');
        } catch (\ErrorException $e) {
            $this->fail('With the default behavior of PHP error handle, no exception should be thrown when warnings, fatals errors occured');
        }

        // 2) Custom error behavior, when errors are transformed into catchable exception, It is Symfony2 do in Symfony\Component\HttpKernel\Debug\ErrorHandler.
        set_error_handler(function($errno, $errstr, $errfile, $errline) {
            throw new \ErrorException($errstr, $errno, 0, $errfile, $errline);
        });

        try {
            file_get_contents('/ThisIsAFileWhichNotExists');
            $this->fail('With a custom error handler as defined above, all traditional errors (E_ERROR, E_PARSE, E_CORE_ERROR...) should be transformed as ErrorException');
        } catch (\ErrorException $e) {
        }
    }
}


