<?php

/*
 * This file is part of Saft.
 *
 * (c) Konrad Abicht <hi@inspirito.de>
 * (c) Natanael Arndt <arndt@informatik.uni-leipzig.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Saft\Sparql\Test\Result;

use Saft\Rdf\Test\TestCase;
use Saft\Sparql\Result\ValueResultImpl;

abstract class ValueResultAbstractTest extends TestCase
{
    /**
     * @param  $mixed $scalar
     *
     * @return Result An instance Of ValueResult
     */
    abstract public function newInstance($scalar);

    public function setUp()
    {
        $this->fixture = new ValueResultImpl(0);
    }

    /**
     * Tests for __construct.
     */
    public function testConstructor()
    {
        $this->fixture = new ValueResultImpl(0);
        $this->assertEquals(0, $this->fixture->getValue());
    }

    public function testConstructorNonScalarParameter()
    {
        $this->setExpectedException('Exception');

        $this->fixture = new ValueResultImpl([]);
    }

    /*
     * Tests for isEmptyResult
     */

    public function testIsEmptyResult()
    {
        $this->assertFalse($this->fixture->isEmptyResult());
    }

    /**
     * Tests for isSetResult.
     */
    public function testIsSetResult()
    {
        $this->assertFalse($this->fixture->isSetResult());
    }

    /**
     * Tests for isStatementSetResult.
     */
    public function testisStatementSetResult()
    {
        $this->assertFalse($this->fixture->isStatementSetResult());
    }

    /**
     * Tests for isValueResult.
     */
    public function testIsValueResult()
    {
        $this->assertTrue($this->fixture->isValueResult());
    }
}
