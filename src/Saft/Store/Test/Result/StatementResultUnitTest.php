<?php

namespace Saft\Store\Test\Result;

use Saft\Rdf\StatementImpl;
use Saft\Rdf\AnyPatternImpl;
use Saft\Store\Result\StatementResult;
use Saft\Test\TestCase;

class StatementResultUnitTest extends TestCase
{
    /**
     *
     */
    public function setUp()
    {
        parent::setUp();

        $this->fixture = new StatementResult();
    }

    public function testConstructorNonIteratorInstance()
    {
        $this->setExpectedException('Exception');

        $this->fixture = new StatementResult('invalid');
    }

    /**
     * Tests append
     */

    public function testAppend()
    {
        $statement = new StatementImpl(new AnyPatternImpl(), new AnyPatternImpl(), new AnyPatternImpl());

        $this->fixture->append($statement);
    }

    public function testAppendInvalidStatement()
    {
        $this->setExpectedException('Exception');

        $this->fixture->append(null);
    }

    /**
     * Tests that class exists
     */
    public function testExistense()
    {
        $this->assertTrue(class_exists('\Saft\Store\Result\StatementResult'));
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
        $this->assertTrue($this->fixture->isStatementResult());
    }

    /**
     * Tests isValueResult
     */

    public function testIsValueResult()
    {
        $this->assertFalse($this->fixture->isValueResult());
    }
}
