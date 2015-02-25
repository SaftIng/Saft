<?php

namespace Saft\Rdf;

class TripleTest extends \PHPUnit_Framework_TestCase
{   
    /**
     * 
     */
    public function setUp()
    {
        parent::setUp();
        
        $this->fixture = new \Saft\Rdf\Triple(
            new \Saft\Rdf\NamedNode('http://saft/test/subject'),
            new \Saft\Rdf\NamedNode('http://saft/test/predicate'),
            new \Saft\Rdf\NamedNode('http://saft/test/object')
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
        $this->assertFalse($this->fixture->isQuad());
    }
    
    /**
     * Tests isTriple
     */
    public function testIsTriple()
    {
        $this->assertTrue($this->fixture->isTriple());
    }
}
