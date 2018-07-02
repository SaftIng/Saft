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
use Saft\Rdf\Statement;
use Saft\Sparql\Result\StatementResult;

abstract class AbstractStatementResultTest extends TestCase
{
    /**
     * @return StatementResult
     */
    abstract protected function getInstance($list = []): StatementResult;

    public function setUp()
    {
        parent::setUp();

        $this->fixture = $this->getInstance();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Parameter $array must only contain Statement instances.
     */
    public function testConstructor()
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
        $this->assertFalse($this->fixture->isSetResult());
    }

    /*
     * Tests for isStatementSetResult
     */

    public function testIsStatementSetResult()
    {
        $this->assertTrue($this->fixture->isStatementSetResult());
    }

    /*
     * Tests for isValueResult
     */

    public function testIsValueResult()
    {
        $this->assertFalse($this->fixture->isValueResult());
    }


    public function testCurrentAndNextAndValid()
    {
        $this->fixture = $this->getInstance([
            $this->statementFactory->createStatement(
                $this->nodeFactory->createNamedNode('http://s'),
                $this->nodeFactory->createNamedNode('http://p'),
                $this->nodeFactory->createNamedNode('http://o')
            )
        ]);

        $this->assertTrue($this->fixture->current() instanceof Statement);
        $this->assertTrue($this->fixture->valid());

        $this->fixture->next();
        $this->assertFalse($this->fixture->valid());
        $this->assertNull($this->fixture->current());
    }

    public function testToArray()
    {
        $this->fixture = $this->getInstance([
            $this->statementFactory->createStatement(
                $this->nodeFactory->createNamedNode('http://s'),
                $this->nodeFactory->createNamedNode('http://p'),
                $this->nodeFactory->createNamedNode('http://o')
            )
        ]);

        $this->assertEquals(
            [
                [
                    's' => 'http://s',
                    'p' => 'http://p',
                    'o' => 'http://o',
                ]
            ],
            $this->fixture->toArray()
        );
    }
}
