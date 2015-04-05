<?php

namespace Saft\Rdf\Test;

use Saft\Rdf\VariableImpl;

class VariableImplUnitTest extends VariableAbstractTest
{

    /**
     * Return a new instance of VariableImpl
     */
    public function newInstance($name)
    {
        return new VariableImpl($name);
    }

    /**
     * Tests check
     * What is this check for? There shouldn't be a method check
     */
    public function testCheck()
    {
        $fixture = $this->newInstance('?irgendwas');
        $this->assertTrue($fixture->check('?s'));
        $this->assertTrue($fixture->check('?longVariable'));

        $this->assertFalse($fixture->check('not a ?variable'));
    }

    public function testToNQuads()
    {
        $fixture = $this->newInstance('?s');
        $this->assertEquals($fixture->toNQuads(), '?s');
    }
}
