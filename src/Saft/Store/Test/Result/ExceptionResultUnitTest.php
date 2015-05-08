<?php

namespace Saft\Store\Test\Result;

use Saft\Store\Result\ExceptionResult;

class ExceptionResultUnitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Contains an instance of the class to test.
     *
     * @var mixed
     */
    protected $fixture;

    /**
     *
     */
    public function setUp()
    {
        $this->fixture = new ExceptionResult(new \Exception(''));
    }

    /**
     * Tests __construct
     */

    public function testConstructor()
    {
        $this->fixture = new ExceptionResult(new \Exception(''));
    }

    /**
     * Tests that class exists
     */
    public function testExistense()
    {
        $this->assertTrue(class_exists('\Saft\Store\Result\ExceptionResult'));
    }

    /**
     * Tests isExceptionResult
     */

    public function testIsExceptionResult()
    {
        $this->assertTrue($this->fixture->isExceptionResult());
    }

    /**
     * Tests isSetResult
     */

    public function testIsSetResult()
    {
        $this->assertFalse($this->fixture->isSetResult());
    }

    /**
     * Tests isStatementResult
     */

    public function testIsStatementResult()
    {
        $this->assertFalse($this->fixture->isStatementResult());
    }

    /**
     * Tests isValueResult
     */

    public function testIsValueResult()
    {
        $this->assertFalse($this->fixture->isValueResult());
    }
}
