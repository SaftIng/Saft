<?php

namespace Saft\Store\Test\Result;

use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Store\Result\SetResult;
use Saft\Test\TestCase;

class SetResultUnitTest extends TestCase
{
    /**
     *
     */
    public function setUp()
    {
        parent::setUp();

        $this->fixture = new SetResult();
    }

    /**
     * Tests __construct
     */

    public function testConstructor()
    {
        $this->fixture = new SetResult(new ArrayStatementIteratorImpl(array()));
    }

    public function testConstructorNonIteratorInstance()
    {
        $this->setExpectedException('Exception');

        $this->fixture = new SetResult('invalid');
    }

    /**
     * Tests append
     */

    public function testAppend()
    {
        $this->fixture->append(null);
    }

    /**
     * Tests that class exists
     */
    public function testExistense()
    {
        $this->assertTrue(class_exists('\Saft\Store\Result\SetResult'));
    }

    /**
     * Tests isSetResult
     */

    public function testIsSetResult()
    {
        $this->assertTrue($this->fixture->isSetResult());
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
