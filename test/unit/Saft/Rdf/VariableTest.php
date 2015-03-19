<?php

namespace Saft\Rdf;

class VariableTest extends \PHPUnit_Framework_TestCase
{

    protected $testUri = 'http://saft/test/';

    /**
     *
     */
    public function setUp()
    {
        parent::setUp();

        $this->fixture = new \Saft\Rdf\Variable('?s');
    }

    /**
     *
     */
    public function tearDown()
    {
        parent::tearDown();
    }

    /**
     * Tests check
     */
    public function testCheck()
    {
        $this->assertTrue($this->fixture->check('?s'));
        $this->assertTrue($this->fixture->check('?longVariable'));

        $this->assertFalse($this->fixture->check('not a ?variable'));
    }

    /**
     * Tests getValue
     */
    public function testGetValue()
    {
        $this->fixture = new \Saft\Rdf\Variable('?s');
        
        $this->assertEquals('?s', $this->fixture->getValue());
    }

    /**
     * Tests instanciation
     */
    public function testInstanciation()
    {
        $this->fixture = new \Saft\Rdf\Variable('?foo');
    }
    
    public function testInstanciationInvalidValue()
    {
        $this->setExpectedException('\Exception');
        
        $this->fixture = new \Saft\Rdf\Variable(2);
    }

    public function testInstanciationNull()
    {
        $this->fixture = new \Saft\Rdf\NamedNode(null);
        $this->assertEquals(null, $this->fixture->getValue());
    }

    /**
     * Tests isBlank
     */
    public function testIsBlank()
    {
        $this->assertFalse($this->fixture->isBlank());
    }

    /**
     * Tests isConcrete
     */
    public function testIsConcrete()
    {
        $this->assertFalse($this->fixture->isConcrete());
    }

    /**
     * Tests isLiteral
     */
    public function testIsLiteral()
    {
        $this->assertFalse($this->fixture->isLiteral());
    }

    /**
     * Tests isNamed
     */
    public function testIsNamed()
    {
        $this->assertFalse($this->fixture->isNamed());
    }

    /**
     * Tests isVariable
     * 
     */
    public function testIsVariable()
    {
        $this->assertTrue($this->fixture->isVariable());
    }
}
