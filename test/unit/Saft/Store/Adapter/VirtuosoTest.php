<?php

namespace Store\Store\Adapter;

class VirtuosoTest extends \Saft\TestCase
{
    public function setUp()
    {   
        parent::setUp();
        
        if ("virtuoso" === $this->_envStoreBackend) {
            $this->_fixture = new \Saft\Store\Adapter\Virtuoso($this->_storeConfig);
        } else {
            $this->markTestSkipped(
                "ENABLE_ENV_STOREBACKEND was not set to virtuoso"
            );
        }
    }
    
    /**
     * function addMultipleTriples
     */
    
    public function testAddMultipleStmts()
    {
        // graph is empty
        $this->assertEquals(0, $this->_fixture->getTripleCount($this->_testGraphUri));
        
        // 2 triples
        $multipleTriples = array(
            array("http://s/", "http://p/", array("type" => "uri", "value" => "http://test/uri")),
            array("http://s/", "http://p/", array("type" => "literal", "value" => "test literal"))
        );
        
        // add triples
        $this->_fixture->addMultipleTriples($this->_testGraphUri, $multipleTriples);
        
        // graph has two entries
        $this->assertEquals(2, $this->_fixture->getTripleCount($this->_testGraphUri));
    }
    
    public function testAddMultipleStmts_languageTags()
    {
        // graph is empty
        $this->assertEquals(0, $this->_fixture->getTripleCount($this->_testGraphUri));
        
        // 2 triples
        $multipleTriples = array(
            array(
                "http://s/", 
                "http://p/", 
                array("lang" => "en", "datatype" => null, "type" => "literal", "value" => "val EN")
            ),
            array(
                "http://s/", 
                "http://p/", 
                array("lang" => "de", "datatype" => null, "type" => "literal", "value" => "val De")
            )
        );
        
        // add triples
        $this->_fixture->addMultipleTriples($this->_testGraphUri, $multipleTriples);
        
        // graph has two entries
        $this->assertEquals(2, $this->_fixture->getTripleCount($this->_testGraphUri));
    }
    
    public function testAddMultipleStmts_massiveStmtArray()
    {
        // graph is empty
        $this->assertEquals(0, $this->_fixture->getTripleCount($this->_testGraphUri));
        
        // create a huge amount of triples
        $multipleTriples = array();
        
        for($i = 0; $i < 10000; ++$i) {
            $multipleTriples[] = array(
                "http://s/$i",
                "http://p/", 
                array("type" => "uri", "value" => "http://test/uri")
            );
        }
        
        // add created triples
        $this->_fixture->addMultipleTriples($this->_testGraphUri, $multipleTriples);
        
        // graph has two entries
        $this->assertEquals(10000, $this->_fixture->getTripleCount($this->_testGraphUri));
    }
    
    /**
     * function dropGraph
     */
    
    public function testAddStmt()
    {
        // graph is empty
        $this->assertEquals(0, $this->_fixture->getTripleCount($this->_testGraphUri));
        
        // add 1 triple
        $this->_fixture->addTriple(
            $this->_testGraphUri,
            "http://s/",
            "http://p/",
            array("lang" => "en", "datatype" => null, "type" => "literal", "value" => "val EN")
        );
        
        $this->assertEquals(1, $this->_fixture->getTripleCount($this->_testGraphUri));
    }
    
    /**
     * function dropGraph
     */
    
    public function testDropGraph()
    {
        $this->_fixture->dropGraph($this->_testGraphUri);
        
        $this->assertFalse(
            $this->_fixture->isGraphAvailable($this->_testGraphUri)
        );
        
        $this->_fixture->addGraph($this->_testGraphUri);
        
        $this->assertTrue(
            $this->_fixture->isGraphAvailable($this->_testGraphUri)
        );
        
        $this->_fixture->dropGraph($this->_testGraphUri);
        
        $this->assertFalse(
            $this->_fixture->isGraphAvailable($this->_testGraphUri)
        );
    }
    
    /**
     * function dropMultipleTriples
     */
    
    public function testDropMultipleStmts()
    {
        /**
         * Create some test data
         */
        $this->assertEquals(0, $this->_fixture->getTripleCount($this->_testGraphUri));
        
        // 2 triples
        $multipleTriples = array(
            array("http://s/", "http://p/", array("type" => "uri", "value" => "http://test/uri")),
            array("http://s/", "http://p/", array("type" => "literal", "value" => "test literal"))
        );
        $this->_fixture->addMultipleTriples($this->_testGraphUri, $multipleTriples);
        $this->assertEquals(2, $this->_fixture->getTripleCount($this->_testGraphUri));
        
        /**
         * drop all triples
         */
        $this->_fixture->dropMultipleTriples(
            $this->_testGraphUri, 
            $multipleTriples
        );
        
        $this->assertEquals(0, $this->_fixture->getTripleCount($this->_testGraphUri));
    }
    
    /**
     * function dropTriple
     */
    
    public function testDropStmt()
    {
        /**
         * Create some test data
         */
        $this->assertEquals(0, $this->_fixture->getTripleCount($this->_testGraphUri));
        
        // 2 triples
        $multipleTriples = array(
            array("http://s/", "http://p/", array("type" => "uri", "value" => "http://test/uri")),
            array("http://s/", "http://p/", array("type" => "literal", "value" => "test literal"))
        );
        $this->_fixture->addMultipleTriples($this->_testGraphUri, $multipleTriples);
        $this->assertEquals(2, $this->_fixture->getTripleCount($this->_testGraphUri));
        
        /**
         * drop one triple
         */
        $this->_fixture->dropTriple(
            $this->_testGraphUri, 
            "http://s/", 
            "http://p/", 
            array("type" => "literal", "value" => "test literal")
        );
        
        $this->assertEquals(1, $this->_fixture->getTripleCount($this->_testGraphUri));
    }
    
    /**
     * 
     * @param
     * @return
     * @throw
     */
    public function testExistence()
    {
        $this->assertTrue(
            class_exists("\Saft\Store\Adapter\Virtuoso")
        );
    }
    
    /**
     * function getAvailableGraphs
     */
    
    public function testGetAvailableGraphs()
    {
        // get graph list
        $graphUris = $this->_fixture->getAvailableGraphUris();
        
        // alternative way to get the list
        $query = $this->_fixture->executeQuery(
            "SELECT ID_TO_IRI(REC_GRAPH_IID) as graph 
               FROM DB.DBA.RDF_EXPLICITLY_CREATED_GRAPH", "sql"
        );
        
        $graphsToCheck = array();
        foreach ($query->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $graphsToCheck[$row["graph"]] = $row["graph"];
        }
        
        $this->assertEqualsArrays(
            $graphUris,
            $graphsToCheck
        );
    }
    
    /**
     * function getTripleCount
     */
    
    public function testGetTripleCount()
    {
        // graph is empty
        $this->assertEquals(0, $this->_fixture->getTripleCount($this->_testGraphUri));
        
        // add 3 triples to the graph
        $this->_fixture->addMultipleTriples($this->_testGraphUri, array(
            array("http://s/", "http://p/", array("type" => "uri", "value" => "http://o/")),
            array("http://s/", "http://p/", array("type" => "uri", "value" => "http://o/1")),
            array("http://s/", "http://p/", array("type" => "uri", "value" => "http://o/2"))
        ));
        
        // graph has to contain 3 triples
        $this->assertEquals(3, $this->_fixture->getTripleCount($this->_testGraphUri));
    }
    
    /**
     * function sparql
     */
    
    public function testSparql()
    {
        $this->assertEquals(0, $this->_fixture->getTripleCount($this->_testGraphUri));
        
        $this->assertEquals(
            array(),
            $this->_fixture->sparql("SELECT ?s ?p ?o WHERE {?s ?p ?o.}")
        );
        
        // add 3 triples to the graph
        $multipleTriples = array(
            array("http://s/", "http://p/", array("type" => "uri", "value" => "http://o/")),
            array("http://s/", "http://p/", array("type" => "uri", "value" => "http://o/1")),
            array("http://s/", "http://p/", array("type" => "uri", "value" => "http://o/2"))
        );
        $this->_fixture->addMultipleTriples($this->_testGraphUri, $multipleTriples);
        
        $this->assertEquals(3, $this->_fixture->getTripleCount($this->_testGraphUri));
        
        $this->assertEquals(
            array(array(
                "s" => "http://s/", "p" => "http://p/", "o" => "http://o/"
            ), array(
                "s" => "http://s/", "p" => "http://p/", "o" => "http://o/1"
            ), array(
                "s" => "http://s/", "p" => "http://p/", "o" => "http://o/2"
            )),
            $this->_fixture->sparql(
                "SELECT ?s ?p ?o 
                   FROM <" . $this->_testGraphUri . "> 
                  WHERE {?s ?p ?o.}"
            )
        );
    }    
    
    public function testSparql_differentResultTypes()
    {
        $this->assertEquals(
            array(),
            $this->_fixture->sparql("SELECT ?s ?p ?o WHERE {?s ?p ?o.}")
        );
        
        $this->assertEquals(
            array(),
            $this->_fixture->sparql("SELECT ?s ?p ?o WHERE {?s ?p ?o.}", array(
                "resultType" => "array"
            ))
        );
    }
    
    public function testSparql_emptyResult()
    {
        $this->assertEquals(
            array(),
            $this->_fixture->sparql("SELECT ?s ?p ?o WHERE {?s ?p ?o.}")
        );
    }
    
    public function testSparql_extendedResult()
    {
        // 2 triples
        $multipleTriples = array(
            array(
                "http://s/",
                "http://p/",
                array("lang" => "en", "datatype" => null, "type" => "literal", "value" => "val EN")
            ),
            array(
                "http://s/",
                "http://p/",
                array("lang" => "de", "datatype" => "http://www.w3.org/2001/XMLSchema#string", 
                      "type" => "literal", "value" => "val DE")
            ),
            array(
                "http://s/",
                "http://p/",
                array(/* no lang */ "datatype" => "http://www.w3.org/2001/XMLSchema#integer", 
                     "type" => "literal", "value" => "1337")
            ),
            array(
                "http://s/",
                "http://p/2",
                // test casting if value is an integer, then a boolean 0 will
                // be in the result an item of datatype xmls:integer!
                array(/* no lang */ "datatype" => "http://www.w3.org/2001/XMLSchema#boolean", 
                      "type" => "literal", "value" => "0")
            ),
            array(
                "http://s/",
                "http://p/3",
                // it seems that either Virtuoso or ODBC convert a xmls:boolean
                // value to an integer later on. So we will cast it internally
                // to an string, to keep the value, but, unfortunately, we lost
                // the datatype too. 
                array(/* no lang */ "datatype" => "http://www.w3.org/2001/XMLSchema#boolean", 
                      "type" => "literal", "value" => "false")
            )
        );
        
        // add triples
        $this->_fixture->addMultipleTriples($this->_testGraphUri, $multipleTriples);
       
        $this->assertEquals(
            array(
                "head" => array(
                    "link" => array(),
                    "vars" => array("s", "p", "o")
                ),
                "results" => array(
                    "distinct"  => false,
                    "ordered"   => true,
                    "bindings"  => array(
                        array(
                            "s" => array(
                                "type"  => "uri",
                                "value" => "http://s/"
                            ),
                            "p" => array(
                                "type"  => "uri",
                                "value" => "http://p/"
                            ),
                            "o" => array(
                                "datatype"  => "http://www.w3.org/2001/XMLSchema#integer",
                                "type"      => "typed-literal",
                                "value"     => "1337"
                            )
                        ),
                        array(
                            "s" => array(
                                "type"  => "uri",
                                "value" => "http://s/"
                            ),
                            "p" => array(
                                "type"  => "uri",
                                "value" => "http://p/"
                            ),
                            "o" => array(
                                "type"      => "literal",
                                "value"     => "val EN",
                                "xml:lang"  => "en"
                            )
                        ),
                        array(
                            "s" => array(
                                "type"  => "uri",
                                "value" => "http://s/"
                            ),
                            "p" => array(
                                "type"  => "uri",
                                "value" => "http://p/"
                            ),
                            "o" => array(
                                "datatype"  => "http://www.w3.org/2001/XMLSchema#string",
                                // there is NO language set! > "lang" => "de",
                                "type"      => "typed-literal",
                                "value"     => "val DE"
                            )
                        ),
                        /**
                         * http//p/2
                         */
                        array(
                            "s" => array(
                                "type"  => "uri",
                                "value" => "http://s/"
                            ),
                            "p" => array(
                                "type"  => "uri",
                                "value" => "http://p/2"
                            ),
                            "o" => array(
                                // the value was 0, but now its false. That's right,
                                // because boolean is either false or true. But we
                                // allow this in the definition, but cast it later on
                                // to keep valid values
                                "datatype"  => "http://www.w3.org/2001/XMLSchema#string",
                                "type"      => "typed-literal",
                                "value"     => "false"
                            )
                        ),
                        /**
                         * http//p/3
                         */
                        array(
                            "s" => array(
                                "type"  => "uri",
                                "value" => "http://s/"
                            ),
                            "p" => array(
                                "type"  => "uri",
                                "value" => "http://p/3"
                            ),
                            "o" => array(
                                "datatype"  => "http://www.w3.org/2001/XMLSchema#string",
                                "type"      => "typed-literal",
                                "value"     => "false"
                            )
                        )
                    )
                )
            ),
            $this->_fixture->sparql(
                "SELECT ?s ?p ?o FROM <". $this->_testGraphUri ."> WHERE {?s ?p ?o.}", 
                array("resultType" => "extended")
            )
        );
    }
    
    public function testSparql_invalidQuery()
    {        
        $this->setExpectedException("\Exception");
        
        $this->_fixture->sparql("invalid SPARQL query");
    }
    
    public function testSparql_invalidResultType()
    {        
        $this->setExpectedException("\Exception");
        
        $this->_fixture->sparql(
            "SELECT ?s ?p ?o FROM <". $this->_testGraphUri ."> WHERE {?s ?p ?o.}", 
            array("resultType" => "invalid")
        );
    }  
}
