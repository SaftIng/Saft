<?php

namespace Saft\Store\Test\Result;

use Saft\Rdf\AnyPatternImpl;
use Saft\Rdf\StatementImpl;
use Saft\Store\Result\StatementResult;
use Saft\Test\TestCase;

abstract class StatementSetResultAbstractTest extends TestCase
{
    /**
     * @return Result
     */
    abstract public function newInstance($list);

    /*
     * Tests for isSetResult
     */

    public function testIsSetResult()
    {
        $list = $this->getMockForAbstractClass('\Iterator');
        $this->fixture = $this->newInstance($list);

        $this->assertFalse($this->fixture->isSetResult());
    }

    /*
     * Tests for isStatementSetResult
     */

    public function testIsStatementSetResult()
    {
        $list = $this->getMockForAbstractClass('\Iterator');
        $this->fixture = $this->newInstance($list);

        $this->assertTrue($this->fixture->isStatementSetResult());
    }

    /*
     * Tests for isValueResult
     */

    public function testIsValueResult()
    {
        $list = $this->getMockForAbstractClass('\Iterator');
        $this->fixture = $this->newInstance($list);

        $this->assertFalse($this->fixture->isValueResult());
    }
}
