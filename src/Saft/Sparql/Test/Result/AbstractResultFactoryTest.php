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
use Saft\Sparql\Result\EmptyResult;
use Saft\Sparql\Result\ResultFactory;
use Saft\Sparql\Result\SetResult;
use Saft\Sparql\Result\StatementResult;
use Saft\Sparql\Result\ValueResult;

abstract class AbstractResultFactoryTest extends TestCase
{
    abstract protected function getInstance(): ResultFactory;

    public function setUp()
    {
        parent::setUp();

        $this->fixture = $this->getInstance();
    }

    public function testCreateEmptyResult()
    {
        $this->assertTrue($this->getInstance()->createEmptyResult() instanceof EmptyResult);
    }

    public function testCreateSetResult()
    {
        $this->assertTrue($this->getInstance()->createSetResult() instanceof SetResult);
        $this->assertTrue($this->getInstance()->createSetResult([]) instanceof SetResult);
    }

    public function testCreateStatementResult()
    {
        $this->assertTrue($this->getInstance()->createStatementResult() instanceof StatementResult);
        $this->assertTrue($this->getInstance()->createStatementResult([]) instanceof StatementResult);
    }

    public function testValueResult()
    {
        $this->assertTrue($this->getInstance()->createValueResult(1) instanceof ValueResult);
    }
}
