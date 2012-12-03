<?php

namespace Rouffj\Tests\Symfony\Form;

use Rouffj\Tests\TestCase;

class PHPTest extends TestCase
{
    public function doSetUp()
    {
    }

    /**
     * @link: http://stackoverflow.com/questions/3797239/php-array-insert-new-item-in-any-position
     */
    public function testHowToInsertIntoArrayNewItemInAnyPosition()
    {
        $original = array( 'a','b','c','d','e' );
        $expected = array('a', 'b', 'c', 'x', 'd', 'e');
        $inserted = array( 'x' );
        $removedItems = array_splice($original, 3, 0, $inserted);
        $this->assertEquals($expected, $original);
        $this->assertEquals(array(), $removedItems, 'expected is empty array because no item is removed (third param contains 0)');
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

    public function testHowToKnowIfADateIsDuringWinterTime()
    {
        $winterTime = 0;
        $summerTime = 1;
        $tz = new \DateTimeZone('Europe/Paris');
        $oct26 = new \DateTime('2012-10-26 00:00', $tz);
        $oct28_1h = new \DateTime('2012-10-28 01:59', $tz);
        $oct28_2h = new \DateTime('2012-10-28 02:00', $tz);
        $dec25 = new \DateTime('2012-12-25 00:00', $tz);

        $this->assertEquals($summerTime, $oct26->format('I'));
        $this->assertEquals($summerTime, $oct28_1h->format('I'));
        $this->assertEquals($winterTime, $oct28_2h->format('I'));
        $this->assertEquals($winterTime, $dec25->format('I'));
    }

    public function testHowToReplaceCompleteSubtringFromString()
    {
        $this->assertEquals('Hello John Carter !!', strtr('Hello Joseph Rouff !!', array('Joseph Rouff' => 'John Carter')));
    }

    /**
     * usecases:
     * - Retrieve the variable parts of an exception message (eg: the file "Dir/foo.php" is not valid)
     *
     * @link http://stackoverflow.com/questions/413071/regex-to-get-string-between-curly-braces-i-want-whats-between-the-curly-brace#answer-413077
     */
    public function testHowToGetStringBetweenDelimiters()
    {
        $matches = array();

        $msg = 'Cannot import resource "src/MyBundle/routes.yml" from "app/config/routing.yml"';
        preg_match_all('/"(.*?)"/', $msg, $matches);
        list($importedFile, $originalFile) = $matches[1];
        $this->assertEquals('src/MyBundle/routes.yml', $importedFile);
        $this->assertEquals('app/config/routing.yml', $originalFile);
    }
}


