<?php
namespace Saft\Rdf\Test;

abstract class VariableAbstractTest extends \PHPUnit_Framework_TestCase
{

    /**
     * An abstract method which returns new instances of Variable
     * @todo The factory method approach could also be extended to use a factory object
     */
    abstract public function newInstance($name);

    /**
     * Tests getValue
     */
    public function testGetValue()
    {
        $fixture = $this->newInstance('?s');

        $this->assertEquals('?s', $fixture->getValue());
    }

    /**
     * Tests instanciation
     */
    public function testInstanciation()
    {
        $this->newInstance('?foo');
    }

    public function testInstanciationInvalidValue()
    {
        $this->setExpectedException('\Exception');

        $this->newInstance(2);
    }

    public function testInstanciationNull()
    {
        $fixture = $this->newInstance(null);
        $this->assertTrue(null !== $fixture->getValue());
    }

    /**
     * Tests isBlank
     */
    public function testIsBlank()
    {
        $fixture = $this->newInstance('?s');
        $this->assertFalse($fixture->isBlank());
    }

    /**
     * Tests isConcrete
     */
    public function testIsConcrete()
    {
        $fixture = $this->newInstance('?s');
        $this->assertFalse($fixture->isConcrete());
    }

    /**
     * Tests isLiteral
     */
    public function testIsLiteral()
    {
        $fixture = $this->newInstance('?s');
        $this->assertFalse($fixture->isLiteral());
    }

    /**
     * Tests isNamed
     */
    public function testIsNamed()
    {
        $fixture = $this->newInstance('?s');
        $this->assertFalse($fixture->isNamed());
    }

    /**
     * Tests isVariable
     *
     */
    public function testIsVariable()
    {
        $fixture = $this->newInstance('?s');
        $this->assertTrue($fixture->isVariable());
    }

}