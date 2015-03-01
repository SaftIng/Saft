<?php

namespace Saft\Store;

class DispenseableResourceTest extends \Saft\TestCase
{
    public function setUp()
    {   
        parent::setUp();
        
        $this->_store->addGraph($this->_testGraphUri);
        
        // init instance of DispenseableResource
        $this->_fixture = new \Saft\Store\DispenseableResource(
            $this->_store->getGraph($this->_testGraphUri)
        );
        
        // remove test namespace from global namespace registry of EasyRdf\RdfNamespace
        \EasyRdf\RdfNamespace::delete("enable");
    }  
    
    /**
     * function __get
     */
     
    public function test__getIsSetNot()
    {
        $this->_fixture->init();
        
        $this->assertFalse(
            isset($this->_fixture->foo)
        );
    }
     
    public function test__getValue()
    {
        $this->assertFalse(
            isset($this->_fixture->foo)
        );
        
        $this->_fixture->init();        
        
        $this->assertFalse(
            isset($this->_fixture->foo)
        );
        
        $this->_fixture->foo = "0";
        
        $this->assertEquals(
            $this->_fixture->foo,
            "0"
        );
    }
    
    /**
     * function __set
     */
     
    public function test__set()
    {
        $this->_fixture->init();
        
        $this->_fixture->foo = 0;
    }
    
    /**
     * function addNamespace
     */
     
    public function testAddNamespace()
    {
        $this->_fixture->addNamespace("enable", "http://enable/");
        
        $this->assertEquals(
            "http://enable/",
            \EasyRdf\RdfNamespace::get("enable")
        );
    }
    
    /**
     * function addNamespace
     */
     
    public function testDeleteNamespace()
    {
        $this->_fixture->addNamespace("enable", "http://enable/");
        
        $this->assertEquals(
            "http://enable/",
            \EasyRdf\RdfNamespace::get("enable")
        );
        
        $this->_fixture->deleteNamespace("enable");
        
        $this->assertNull(
            \EasyRdf\RdfNamespace::get("enable")
        );
    }
    
    /**
     * function generateTriples
     */
     
    public function testGenerateTriples()
    {
        $this->_fixture->init();
        
        $this->_fixture->foo = "bar";
        $this->_fixture->foo1 = "bar-1";
        
        $this->assertEquals(
            array(
                array(
                    $this->_fixture->uri, 
                    "a", 
                    array(
                        "type" => "uri",
                        "value" => "http://www.w3.org/1999/02/22-rdf-syntax-ns#resource"
                    )
                ),
                array(
                    $this->_fixture->uri,
                    $this->_fixture->uri . urlencode("foo"),
                    array(
                        "type" => "literal",
                        "value" => "bar"
                    )
                ),
                array(
                    $this->_fixture->uri,
                    $this->_fixture->uri . urlencode("foo1"),
                    array(
                        "type" => "literal",
                        "value" => "bar-1"
                    )
                ),
            ),
            $this->_fixture->generateTriples()
        );
    }
    
    /**
     * function load
     */
     
    public function testLoad()
    {        
        $this->_fixture->init();
        
        $this->_fixture->foo = "bar";
        
        $this->_fixture->save();
        
        $this->_fixture->load($this->_fixture->id);
        
        $this->assertEquals(
            "bar",
            $this->_fixture->foo
        );
    }
     
    public function testLoad_Namespace()
    {        
        $this->_fixture->init();
        
        $this->_fixture->addNamespace("enable", "http://enable/");
        
        $this->_fixture->{"enable:ttt"} = "bar";
        
        $this->_fixture->save();
        
        $this->_fixture->load($this->_fixture->id);
        
        $this->assertEquals(
            "bar",
            $this->_fixture->{"enable:ttt"}
        );
    }
     
    public function testLoad_UnknownNamespace()
    {
        $this->setExpectedException("\Exception");
        
        $this->_fixture->init();
        
        $this->_fixture->{"enable:hihi"} = "bar";
        
        $this->_fixture->save();
    }
    
    /**
     * function init
     */
     
    public function testInit()
    {        
        $this->assertFalse(
            isset($this->_fixture->id)
        );
        
        $this->_fixture->init();
        
        $this->assertTrue(
            isset($this->_fixture->id)
        );
    }
    
    /**
     * function save
     */
     
    public function testSaveSimple()
    {        
        $this->_store->clearGraph($this->_testGraphUri);
        
        $this->_fixture->init();
        
        $this->_fixture->foo = "bar";
        
        $this->_fixture->save();
        
        $result = $this->_store->getGraph($this->_testGraphUri)->sparql(
            "SELECT ?s ?p ?o WHERE {?s ?p ?o.};"
        );
        
        $this->assertEqualsArrays(
            array(
                array(
                    "s" => $this->_fixture->uri,
                    "p" => "a",
                    "o" => "http://www.w3.org/1999/02/22-rdf-syntax-ns#resource"
                ),
                array(
                    "s" => $this->_fixture->uri,
                    "p" => $this->_fixture->uri . "foo",
                    "o" => "bar"
                ),
            ),
            $result
        );
    }
     
    public function testSaveWithNamespaces()
    {
        $this->_store->clearGraph($this->_testGraphUri);
        
        $this->_fixture->init();
        
        $this->_fixture->{"rdf:subject"} = "bar";
        
        $this->_fixture->save();
        
        $result = $this->_store->getGraph($this->_testGraphUri)->sparql(
            "SELECT ?s ?p ?o WHERE {?s ?p ?o.};"
        );
        
        $this->assertEqualsArrays(
            array(
                array(
                    "s" => $this->_fixture->uri,
                    "p" => "a",
                    "o" => "http://www.w3.org/1999/02/22-rdf-syntax-ns#resource"
                ),
                array(
                    "s" => $this->_fixture->uri,
                    "p" => "http://www.w3.org/1999/02/22-rdf-syntax-ns#subject",
                    "o" => "bar"
                ),
            ),
            $result
        );
    }
     
    public function testSaveWithOverriding()
    {
        $this->_store->clearGraph($this->_testGraphUri);
        
        $this->_fixture->init();
        
        $this->_fixture->foo = "bar";
        
        $this->_fixture->save();
        
        /**
         * SPARQL for the first change
         */
        
        $result = $this->_store->getGraph($this->_testGraphUri)->sparql(
            "SELECT ?s ?p ?o WHERE {?s ?p ?o.};"
        );
        
        $this->assertEqualsArrays(
            array(
                array(
                    "s" => $this->_fixture->uri,
                    "p" => "a",
                    "o" => "http://www.w3.org/1999/02/22-rdf-syntax-ns#resource"
                ),
                array(
                    "s" => $this->_fixture->uri,
                    "p" => $this->_fixture->uri . "foo",
                    "o" => "bar"
                ),
            ),
            $result
        );
        
        $this->_fixture->ttt = "foobar";
        
        $this->_fixture->save();
        
        /**
         * SPARQL for the second change
         */
        $result2 = $this->_store->getGraph($this->_testGraphUri)->sparql(
            "SELECT ?s ?p ?o WHERE {?s ?p ?o.};"
        );
        
        $this->assertEqualsArrays(
            array(
                array(
                    "s" => $this->_fixture->uri,
                    "p" => "a",
                    "o" => "http://www.w3.org/1999/02/22-rdf-syntax-ns#resource"
                ),
                array(
                    "s" => $this->_fixture->uri,
                    "p" => $this->_fixture->uri . "foo",
                    "o" => "bar"
                ),
                array(
                    "s" => $this->_fixture->uri,
                    "p" => $this->_fixture->uri . "ttt",
                    "o" => "foobar"
                ),
            ),
            $result2
        );
    }
}
