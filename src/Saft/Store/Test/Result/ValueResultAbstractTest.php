<?php

namespace Saft\Store\Test\Result;

use Saft\Store\Result\ValueResultImpl;
use Saft\Test\TestCase;

abstract class ValueResultAbstractTest extends TestCase
{
    /**
     * @param  $mixed $scalar
     * @return Result An instance Of ValueResult
     */
    abstract public function newInstance($scalar);

    /**
     *
     */
    public function setUp()
    {
        $this->fixture = new ValueResultImpl(0);
    }

    /**
     * Tests for __construct
     */

    public function testConstructor()
    {
        $this->fixture = new ValueResultImpl(0);
    }

    public function testConstructorNonScalarParameter()
    {
        $this->setExpectedException('Exception');

        $this->fixture = new ValueResultImpl(array());
    }

    /**
     * Tests for isSetResult
     */

    public function testIsSetResult()
    {
        $this->assertFalse($this->fixture->isSetResult());
    }

    /**
     * Tests for isStatementSetResult
     */

    public function testisStatementSetResult()
    {
        $this->assertFalse($this->fixture->isStatementSetResult());
    }

    /**
     * Tests for isValueResult
     */

    public function testIsValueResult()
    {
        $this->assertTrue($this->fixture->isValueResult());
    }
}
