<?php

namespace Saft\Rdf\Literal;

class BooleanTest extends \PHPUnit_Framework_TestCase
{   
    /**
     * 
     */
    public function setUp()
    {
        parent::setUp();
        
        $this->fixture = new \Saft\Rdf\Literal(null, null);
    }
    
    /**
     * 
     */
    public function tearDown()
    {
        parent::tearDown();
    }
    
    /**
     * Tests equals
     */
    public function testEquals()
    {
        $this->fixture = new \Saft\Rdf\Literal(true);
        $toCompare = new \Saft\Rdf\Literal(true);
        
        $this->assertTrue($this->fixture->equals($toCompare));
    }
    
    public function testEquals_differentType()
    {
        $this->fixture = new \Saft\Rdf\Literal(1);
        $toCompare = new \Saft\Rdf\Literal(1.0);
        
        $this->assertFalse($this->fixture->equals($toCompare));
    }
    
    /**
     * Tests getDatatype
     */
    public function testGetDatatype_boolean()
    {
        $this->fixture = new \Saft\Rdf\Literal(true);
        
        $this->assertEquals(
            'http://www.w3.org/2001/XMLSchema#boolean',
            $this->fixture->getDatatype()
        );
    }
    
    public function testGetDatatype_langSet()
    {
        $this->fixture = new \Saft\Rdf\Literal('foo', 'en');
        
        $this->assertNull($this->fixture->getDatatype());
    }
    
    public function testGetDatatype_decimal()
    {
        $this->fixture = new \Saft\Rdf\Literal(3.18);
        
        $this->assertEquals(
            'http://www.w3.org/2001/XMLSchema#decimal',
            $this->fixture->getDatatype()
        );
    }
    
    public function testGetDatatype_integer()
    {
        $this->fixture = new \Saft\Rdf\Literal(3);
        
        $this->assertEquals(
            'http://www.w3.org/2001/XMLSchema#integer',
            $this->fixture->getDatatype()
        );
    }
    
    public function testGetDatatype_string()
    {
        $this->fixture = new \Saft\Rdf\Literal('foo');
        
        $this->assertEquals(
            'http://www.w3.org/2001/XMLSchema#string',
            $this->fixture->getDatatype()
        );
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
        $this->assertTrue($this->fixture->isConcrete());
    }
    
    /**
     * Tests isLiteral
     */
    public function testIsLiteral()
    {
        $this->assertTrue($this->fixture->isLiteral());
    }
    
    /**
     * Tests isNamed
     */
    public function testIsNamed()
    {
        $this->assertFalse($this->fixture->isNamed());
    }
    
    /**
     * Tests toNT
     */
    public function testToNT_langAndValueSet()
    {
        $this->fixture = new \Saft\Rdf\Literal('foo', 'en');
        
        $this->assertEquals('"foo"@en', $this->fixture->toNT());
    }
    
    public function testToNT_valueBoolean()
    {
        $this->fixture = new \Saft\Rdf\Literal(true);
        
        $this->assertEquals(
            '"1"^^<http://www.w3.org/2001/XMLSchema#boolean>', 
            $this->fixture->toNT()
        );
    }
    
    public function testToNT_valueInteger()
    {
        $this->fixture = new \Saft\Rdf\Literal(30);
        
        $this->assertEquals(
            '"30"^^<http://www.w3.org/2001/XMLSchema#integer>', 
            $this->fixture->toNT()
        );
    }
    
    public function testToNT_valueNull()
    {
        // TODO: Implement case for getDatatype when value is null.
        $this->setExpectedException('\Exception');
        
        $this->fixture = new \Saft\Rdf\Literal(null);
        
        $this->fixture->toNT();
    }
    
    public function testToNT_valueString()
    {
        $this->fixture = new \Saft\Rdf\Literal('foo');
        
        $this->assertEquals(
            '"foo"^^<http://www.w3.org/2001/XMLSchema#string>', 
            $this->fixture->toNT()
        );
    }
}
