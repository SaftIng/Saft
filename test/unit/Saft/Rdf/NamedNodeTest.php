<?php

namespace Saft\Rdf;

class NamedNodeTest extends \PHPUnit_Framework_TestCase
{   
    protected $testUri = 'http://saft/test/';
    
    /**
     * 
     */
    public function setUp()
    {
        parent::setUp();
        
        $this->fixture = new \Saft\Rdf\NamedNode($this->testUri);
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
        $this->assertFalse($this->fixture->check(""));
        $this->assertFalse($this->fixture->check("http//foobar/"));
        
        $this->assertTrue($this->fixture->check("http:foobar/"));
        $this->assertTrue($this->fixture->check("http://foobar/"));
        $this->assertTrue($this->fixture->check("http://foobar:42/"));
        $this->assertTrue($this->fixture->check("http://foo:bar@foobar/"));
    }
    
    /**
     * Tests instanciation
     */
    public function testInstanciation_invalidUri()
    {
        $this->setExpectedException('\Exception');
        
        $this->fixture = new \Saft\Rdf\NamedNode('foo');
    }
    
    public function testInstanciation_null()
    {
        $this->fixture = new \Saft\Rdf\NamedNode(null);
        $this->assertEquals(null, $this->fixture->getValue());
    }
    
    public function testInstanciation_validUri()
    {
        $this->fixture = new \Saft\Rdf\NamedNode($this->testUri);
        $this->assertEquals($this->testUri, $this->fixture->getValue());
    }
    
    public function testInstanciation_variable()
    {
        $this->fixture = new \Saft\Rdf\NamedNode("?s");
        $this->assertEquals("?s", $this->fixture->getValue());
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
        $this->assertTrue($this->fixture->isNamed());
    }
    
    /**
     * Tests isNamed
     */
    public function testIsVariable()
    {
        $this->assertTrue($this->fixture->isVariable("?s"));
        $this->assertTrue($this->fixture->isVariable("?longVariable"));
        
        $this->assertFalse($this->fixture->isVariable("not a ?variable"));
    }
}
