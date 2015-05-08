<?php

namespace Saft\Store\Test\Result;

use Saft\Store\Result\ValueResult;

class ValueResultUnitTest extends \PHPUnit_Framework_TestCase
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
        $this->fixture = new ValueResult(0);
    }

    /**
     * Tests __construct
     */

    public function testConstructor()
    {
        $this->fixture = new ValueResult(0);
    }

    public function testConstructorNonScalarParameter()
    {
        $this->setExpectedException('Exception');

        $this->fixture = new ValueResult(array());
    }

    /**
     * Tests that class exists
     */
    public function testExistense()
    {
        $this->assertTrue(class_exists('\Saft\Store\Result\ValueResult'));
    }

    /**
     * Tests isErrorResult
     */

    public function testIsExceptionResult()
    {
        $this->assertFalse($this->fixture->isExceptionResult());
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
        $this->assertTrue($this->fixture->isValueResult());
    }
}
