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
use Saft\Sparql\Result\EmptyResultImpl;

class EmptyResultImplTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->fixture = new EmptyResultImpl();
    }

    /*
     * Tests for isEmptyResult
     */

    public function testIsEmptyResult()
    {
        $this->assertTrue($this->fixture->isEmptyResult());
    }

    /*
     * Tests for isSetResult
     */

    public function testIsSetResult()
    {
        $this->assertFalse($this->fixture->isSetResult());
    }

    /*
     * Tests for isStatementSetResult
     */

    public function testIsStatementSetResult()
    {
        $this->assertFalse($this->fixture->isStatementSetResult());
    }

    /*
     * Tests for isValueResult
     */

    public function testIsValueResult()
    {
        $this->assertFalse($this->fixture->isValueResult());
    }
}
