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
        $this->fixture = new \Saft\Rdf\Literal("foo", "en");
        
        $this->assertEquals('"foo"@en', $this->fixture->toNT());
    }
    
    public function testToNT_valueBoolean()
    {
        $this->fixture = new \Saft\Rdf\Literal(true);
        
        $this->assertEquals('"1"', $this->fixture->toNT());
    }
    
    public function testToNT_valueInteger()
    {
        $this->fixture = new \Saft\Rdf\Literal(30);
        
        $this->assertEquals('"30"', $this->fixture->toNT());
    }
    
    public function testToNT_valueNull()
    {
        $this->fixture = new \Saft\Rdf\Literal(null);
        
        $this->assertEquals('""', $this->fixture->toNT());
    }
    
    public function testToNT_valueString()
    {
        $this->fixture = new \Saft\Rdf\Literal("foo");
        
        $this->assertEquals('"foo"', $this->fixture->toNT());
    }
}
