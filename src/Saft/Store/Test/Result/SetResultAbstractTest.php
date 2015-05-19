<?php

namespace Saft\Store\Test\Result;

use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Store\Result\SetResult;
use Saft\Test\TestCase;

abstract class SetResultAbstractTest extends TestCase
{
    /**
     * @param \Iterator $list
     * @return SetResult
     */
    abstract public function newInstance($list);

    /**
     * Tests isSetResult
     */

    public function testIsSetResult()
    {
        $list = $this->getMockForAbstractClass('\Iterator');
        $this->fixture = $this->newInstance($list);

        $this->assertTrue($this->fixture->isSetResult());
    }

    /**
     * Tests isStatementSetResult
     */

    public function testIsStatementSetResult()
    {
        $list = $this->getMockForAbstractClass('\Iterator');
        $this->fixture = $this->newInstance($list);

        $this->assertFalse($this->fixture->isStatementSetResult());
    }

    /**
     * Tests isValueResult
     */

    public function testIsValueResult()
    {
        $list = $this->getMockForAbstractClass('\Iterator');
        $this->fixture = $this->newInstance($list);

        $this->assertFalse($this->fixture->isValueResult());
    }
}
