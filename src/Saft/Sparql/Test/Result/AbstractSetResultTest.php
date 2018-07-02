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
use Saft\Sparql\Result\SetResult;

abstract class AbstractSetResultTest extends TestCase
{
    /**
     * @param \Iterator $list
     *
     * @return SetResult
     */
    abstract public function getInstance($list = []): SetResult;

    public function setUp()
    {
        parent::setUp();

        $this->fixture = $this->getInstance();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage $array must only contain arrays.
     */
    public function testConstructorInvalidList()
    {
        $this->getInstance([1]);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage $array contains entries which are not of type Saft/Rdf/Node.
     */
    public function testConstructorInvalidList2()
    {
        $this->getInstance([
            [1]
        ]);
    }

    /*
     * Tests for isEmptyResult
     */

    public function testIsEmptyResult()
    {
        $this->assertFalse($this->fixture->isEmptyResult());
    }

    /*
     * Tests for isSetResult
     */

    public function testIsSetResult()
    {
        $this->assertTrue($this->fixture->isSetResult());
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


    public function testSetGetVariables()
    {
        $this->assertTrue(empty($this->fixture->getVariables()));

        $this->fixture->setVariables(['s', 'p']);
        $this->assertEquals(['s', 'p'], $this->fixture->getVariables());
    }
}
