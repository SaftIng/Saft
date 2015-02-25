<?php

namespace Saft\Rdf;

class QuadTest extends \PHPUnit_Framework_TestCase
{   
    /**
     * 
     */
    public function setUp()
    {
        parent::setUp();
        
        $this->fixture = new \Saft\Rdf\Quad(
            new \Saft\Rdf\NamedNode('http://saft/test/subject'),
            new \Saft\Rdf\NamedNode('http://saft/test/predicate'),
            new \Saft\Rdf\NamedNode('http://saft/test/object'),
            'http://saft/test/graph'
        );
    }
    
    /**
     * 
     */
    public function tearDown()
    {
        parent::tearDown();
    }
    
    /**
     * Tests isQuad
     */
    public function testIsQuad()
    {
        $this->assertTrue($this->fixture->isQuad());
    }
    
    /**
     * Tests isTriple
     */
    public function testIsTriple()
    {
        $this->assertFalse($this->fixture->isTriple());
    }
}
