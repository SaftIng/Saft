<?php

namespace Saft\Rdf\Test;

use Saft\Rdf\VariableImpl;

class VariableImplUnitTest extends VariableAbstractTest
{
    /**
     * Return a new instance of VariableImpl
     *
     * @param $name optional Name of the variable
     * @return VariableImpl Instance of VariableImpl
     */
    public function newInstance($name = null)
    {
        return new VariableImpl($name);
    }
    
    /**
     * Tests __toString
     */
    public function testToString()
    {
        $fixture = $this->newInstance('?foo');
        $this->assertEquals('foo', (string)$fixture);
    }

    /**
     * Tests check
     * @TODO What is this check for? There shouldn't be a method check
     *       > Who made it? Please ask Konrad about this
     */
    public function testCheck()
    {
        $fixture = $this->newInstance('?irgendwas');
        $this->assertTrue($fixture->check('?s'));
        $this->assertTrue($fixture->check('?longVariable'));

        $this->assertFalse($fixture->check('not a ?variable'));
    }

    /**
     * Tests getValue
     */
    public function testGetValue()
    {
        $fixture = $this->newInstance('?foo');
        $this->assertEquals('foo', $fixture->getValue());
    }
    
    /**
     * Tests instanciation
     */
    public function testGetValueInvalidVariableValue()
    {
        $this->setExpectedException('Exception');
        
        $this->newInstance('foo');
    }

    public function testToNQuads()
    {
        $fixture = $this->newInstance('?s');
        $this->assertEquals($fixture->toNQuads(), '?s');
    }
}
