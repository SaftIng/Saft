<?php

namespace Saft;

class StoreTest extends \Saft\TestCase
{
    /**
     * Generates a bunch of test data, but it also makes sure that there is nothing
     * already in the cache.
     */
    public function generateTestCacheEntries()
    {
        $query = "SELECT ?s ?p ?o
                    FROM <". $this->_testGraphUri .">
                    FROM <". $this->_testGraphUri ."2>
                   WHERE {
                     <http://rdfs.org/sioc/ns#foo> ?p ?o.
                     ?s <http://www.w3.org/2000/01/rdf-schema#label> \"foo\".
                     FILTER (?o < 40)
                   }";
        $result = array("foo" => 1337);

        $queryId = $this->_fixture->getQueryCache()->generateShortId($query);
        $this->assertFalse($this->_fixture->getCache()->get($queryId));
        
        /**
         * graph container
         */
        $graphUris = array(
            $this->_testGraphUri, $this->_testGraphUri . "2"
        );
        $graphIds = array();
        
        foreach($graphUris as $graphUri) {
            $graphIds[] = $this->_fixture->getQueryCache()->generateShortId($graphUri);
        }
        
        foreach($graphIds as $graphId) {
            $this->assertFalse($this->_fixture->getCache()->get($graphId));
        }
        
        /**
         * Generate triple pattern array. 
          
           # Example:
           
            "qryCah--f7bc2ce104" => array (
                0 => "qryCah--f7bc2ce104_*_*_*"
                1 => "qryCah--f7bc2ce104_*_2e393a3c3c_*"
            )
        
        
            # Behind the scenes (what does each entry mean):
            
            "$graphId (hashed graph URI)" => array (
                0 => "$keyPrefix$graphId (hashed graph URI)_Placeholder_Placeholder_Placeholder"
                1 => "$keyPrefix$graphId (hashed graph URI)_Placeholder_2e393a3c3c_Placeholder"
            )
         *
         */
        $sPO = "_". $this->_fixture->getQueryCache()->generateShortId("http://rdfs.org/sioc/ns#foo", false) 
               . "_*_*";
               
        $sLabelFoo = "_*_" . $this->_fixture->getQueryCache()->generateShortId(
                        "http://www.w3.org/2000/01/rdf-schema#label", false
                     ) . "_*";
        
        foreach($graphIds as $graphId) {
            $triplePattern[$graphId] = array(
                $graphId . $sPO, 
                $graphId . $sLabelFoo
            );
            
            $this->assertFalse($this->_fixture->getCache()->get($graphId . $sPO));
            $this->assertFalse($this->_fixture->getCache()->get($graphId . $sLabelFoo));
        }
        
        return array(
            "graphIds"          => $graphIds,       // have cache entries
            "graphUris"         => $graphUris,
            "query"             => $query,
            "queryContainer"    => array(
                "relatedQueryCacheEntries"   => "",
                "graphIds"                   => $graphIds,
                "query"                      => $query,
                "result"                     => $result,
                "triplePattern"              => $triplePattern 
            ),
            "queryId"           => $queryId,        // has cache entry 
            "result"            => $result,
            "triplePattern"     => $triplePattern   // have cache entries
        );
    }
    
    public function setUp()
    {   
        parent::setUp();
        
        $this->_fixture = new \Saft\Store($this->_storeConfig, $this->_cache);
        $this->_fixture->addGraph($this->_testGraphUri);
        
        $this->_cache->clean();
    }
    
    /**
     * 
     */
    public function tearDown()
    { 
        $this->_fixture->dropGraph($this->_testGraphUri);
        $this->_fixture->getCache()->clean();
    
        parent::tearDown();
    }
    
    /**
     * function addGraph
     */
    public function testAddGraph()
    {
        $this->_fixture->dropGraph($this->_testGraphUri);
        
        $this->assertFalse(
            $this->_fixture->isGraphAvailable($this->_testGraphUri)
        );
        
        // clear cache
        $this->_cache->clean();
        
        $this->assertFalse(
            $this->_cache->get("enable_store_availableGraphs")
        );
        
        // add graph
        $this->_fixture->addGraph($this->_testGraphUri);
        
        $this->assertTrue(
            $this->_fixture->isGraphAvailable($this->_testGraphUri)
        );
        
        // check cache for added graph
        $this->assertTrue(
            array_key_exists(
                $this->_testGraphUri, 
                $this->_cache->get("sto". $this->_fixture->getId() ."_availableGraphUris")
            )
        );
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
            array(
                "http://s/", 
                "http://p/", 
                array("type" => "uri", "value" => "http://test/uri")
            ),
            array(
                "http://s/", 
                "http://p/", 
                array("type" => "literal", "value" => "test literal")
            )
        );
        
        // add triples
        $this->_fixture->addMultipleTriples($this->_testGraphUri, $multipleTriples);
        
        // graph contains two entries
        $this->assertEquals(2, $this->_fixture->getTripleCount($this->_testGraphUri));
        
        // execute a query to get the content of the graph
        $graph = $this->_fixture->getGraph($this->_testGraphUri);
        $this->assertEquals(
            array(
                array(
                    "s" => "http://s/", "p" => "http://p/", "o" => "http://test/uri"
                ),
                array(
                    "s" => "http://s/", "p" => "http://p/", "o" => "test literal"
                )
            ),
            $graph->sparql("SELECT ?s ?p ?o WHERE {?s ?p ?o.}")
        );
    }
    
    public function testAddMultipleStmts_focusOnQueryCache()
    {
        $testData = $this->generateTestCacheEntries();
        
        // put test data into the QueryCache
        $this->_fixture->getQueryCache()->rememberQueryResult(
            $testData["query"], $testData["result"]
        );
        
        /**
         * check if everything was created successfully
         */
                
        // graph container
        foreach($testData["graphIds"] as $graphId) {
            $this->assertEquals(
                array($testData["queryId"] => $testData["queryId"]),
                $this->_fixture->getCache()->get($graphId)
            );
        }
        
        // triple pattern
        foreach ($testData["triplePattern"] as $graphId => $triplePattern) {
            foreach ($triplePattern as $pattern) {
                $this->assertEquals(
                    $testData["queryId"], $this->_fixture->getCache()->get($pattern)
                );
            }
        }
        
        // query container
        $this->assertEquals(
            $testData["queryContainer"], $this->_fixture->getCache()->get($testData["queryId"])
        );
        
        /**
         * add triples which have to lead to an invalidation of the previously
         * created cache entries!
         */
        $this->_fixture->addMultipleTriples(
            $this->_testGraphUri, 
            array(
                array(
                    "http://rdfs.org/sioc/ns#foo", 
                    "http://predicate/", 
                    array("type" => "uri", "value" => "http://object")
                ), 
                array(
                    "http://whatever",
                    "http://www.w3.org/2000/01/rdf-schema#label",
                    array("type" => "literal", "value" => "some label")
                )
            )
        );
        
        /**
         * check everything again
         */
        
        // graph container
        foreach($testData["graphIds"] as $graphId) {
            $this->assertFalse(
                $this->_fixture->getCache()->get($graphId)
            );
        }
        
        // triple pattern
        foreach ($testData["triplePattern"] as $graphId => $triplePattern) {
            foreach ($triplePattern as $pattern) {
                $this->assertFalse($this->_fixture->getCache()->get($pattern));
            }
        }
        
        // query container
        $this->assertFalse($this->_fixture->getCache()->get($testData["queryId"]));
    }
    
    /**
     * function addTriple
     */
    
    public function testAddStmt()
    {
        // graph is empty
        $this->assertEquals(0, $this->_fixture->getTripleCount($this->_testGraphUri));
        
        // add triple
        $this->_fixture->addTriple(
            $this->_testGraphUri, 
            "http://s/", "http://p/", array("type" => "uri", "value" => "http://test/uri")
        );
        
        // graph is empty
        $this->assertEquals(1, $this->_fixture->getTripleCount($this->_testGraphUri));
        
        // execute a query to get the content of the graph
        $graph = $this->_fixture->getGraph($this->_testGraphUri);
        $this->assertEquals(
            array(
                array(
                    "s" => "http://s/", "p" => "http://p/", "o" => "http://test/uri"
                )
            ),
            $graph->sparql("SELECT ?s ?p ?o WHERE {?s ?p ?o.}")
        );
    }
    
    public function testAddStmt_focusOnQueryCache()
    {
        $testData = $this->generateTestCacheEntries();
        
        // put test data into the QueryCache
        $this->_fixture->getQueryCache()->rememberQueryResult(
            $testData["query"], $testData["result"]
        );
        
        /**
         * check if everything was created successfully
         */
                
        // graph container
        foreach($testData["graphIds"] as $graphId) {
            $this->assertEquals(
                array($testData["queryId"] => $testData["queryId"]),
                $this->_fixture->getCache()->get($graphId)
            );
        }
        
        // triple pattern
        foreach ($testData["triplePattern"] as $graphId => $triplePattern) {
            foreach ($triplePattern as $pattern) {
                $this->assertEquals(
                    $testData["queryId"], $this->_fixture->getCache()->get($pattern)
                );
            }
        }
        
        // query container
        $this->assertEquals(
            $testData["queryContainer"], $this->_fixture->getCache()->get($testData["queryId"])
        );
        
        /**
         * add a triple which has to lead to an invalidation of the previously
         * created cache data!
         */
        $this->_fixture->addTriple(
            $this->_testGraphUri, 
            "http://rdfs.org/sioc/ns#foo", 
            "http://predicate/",
            array("type" => "uri", "value" => "http://object")
        );
        
        
        /**
         * check everything again
         */
        
        // graph container
        foreach($testData["graphIds"] as $graphId) {
            $this->assertFalse(
                $this->_fixture->getCache()->get($graphId)
            );
        }
        
        // triple pattern
        foreach ($testData["triplePattern"] as $graphId => $triplePattern) {
            foreach ($triplePattern as $pattern) {
                $this->assertFalse($this->_fixture->getCache()->get($pattern));
            }
        }
        
        // query container
        $this->assertFalse($this->_fixture->getCache()->get($testData["queryId"]));
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
    
    public function testDropGraph_focusOnQueryCache()
    {
        $testData = $this->generateTestCacheEntries();
        
        // put test data into the QueryCache
        $this->_fixture->getQueryCache()->rememberQueryResult(
            $testData["query"], $testData["result"]
        );
        
        /**
         * check if everything was created successfully
         */
                
        // graph container
        foreach($testData["graphIds"] as $graphId) {
            $this->assertEquals(
                array($testData["queryId"] => $testData["queryId"]),
                $this->_fixture->getCache()->get($graphId)
            );
        }
        
        // triple pattern
        foreach ($testData["triplePattern"] as $graphId => $triplePattern) {
            foreach ($triplePattern as $pattern) {
                $this->assertEquals(
                    $testData["queryId"], $this->_fixture->getCache()->get($pattern)
                );
            }
        }
        
        // query container
        $this->assertEquals(
            $testData["queryContainer"], $this->_fixture->getCache()->get($testData["queryId"])
        );
        
        
        /**
         * add a triple which has to lead to an invalidation of the previously
         * created cache data!
         */
        $this->_fixture->dropGraph($this->_testGraphUri);
        
        
        /**
         * check everything again
         */
        
        // graph container
        foreach($testData["graphIds"] as $graphId) {
            if ($graphId === 
                $this->_fixture->getQueryCache()->generateShortId($this->_testGraphUri)) {
                $this->assertFalse(
                    $this->_fixture->getCache()->get($graphId)
                );
            }
        }
        
        // triple pattern
        foreach ($testData["triplePattern"] as $graphId => $triplePattern) {
            foreach ($triplePattern as $pattern) {
                $this->assertFalse($this->_fixture->getCache()->get($pattern));
            }
        }
        
        // query container
        $this->assertFalse($this->_fixture->getCache()->get($testData["queryId"]));
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
         * drop multiple triples
         */
        $this->_fixture->dropMultipleTriples(
            $this->_testGraphUri, $multipleTriples
        );
        
        $this->assertEquals(0, $this->_fixture->getTripleCount($this->_testGraphUri));
    }
    
    public function testDropMultipleStmts_focusOnQueryCache()
    {
        $graph = $this->_fixture->getGraph($this->_testGraphUri);
        $this->assertEquals(0, $this->_fixture->getTripleCount($this->_testGraphUri));
        
        
        /**
         * Create some test data, especially for the QueryCache
         */
        $testData = $this->generateTestCacheEntries();
        
        $resourceUri = "http://foo/bar";
        
        // test triples
        $multipleTriples = array(array(
            $resourceUri, 
            "http://www.w3.org/2000/01/rdf-schema#label",
            array("type" => "literal", "value" => "foo's label")
        ));
        $this->_fixture->addMultipleTriples($this->_testGraphUri, $multipleTriples);      
        
        // verify the everything was created as requested, bypass query cache
        $this->assertEquals(
            array(
                array(
                    "s" => $resourceUri,
                    "p" => "http://www.w3.org/2000/01/rdf-schema#label",
                    "o" => "foo's label"
                )
            ),
            // usually sparql function cache query + result, but we force it to 
            // bypass QueryCache 
            $graph->sparql(
                "SELECT ?s ?p ?o WHERE {?s ?p ?o.}", array("useQueryCache" => false)
            )
        );
        
        /**
         * check cache entries; the adding of multiple triples has to lead to
         * an invalidation of a couple of cache entries
         */
         
        // graph container
        foreach($testData["graphIds"] as $graphId) {
            $this->assertFalse($this->_fixture->getCache()->get($graphId));
        }
        
        // triple pattern
        foreach ($testData["triplePattern"] as $graphId => $triplePattern) {
            foreach ($triplePattern as $pattern) {
                $this->assertFalse($this->_fixture->getCache()->get($pattern));
            }
        }
        
        // query container
        $this->assertFalse($this->_fixture->getCache()->get($testData["queryId"]));  
        
        /**
         * get information about the main resource to let the query cache save the
         * according cache information
         */
        $this->assertEquals(
            array(
                array(
                    $resourceUri,
                    "http://www.w3.org/2000/01/rdf-schema#label",
                    array(
                        "datatype" => null, 
                        "lang" => null, 
                        "type" => "literal", 
                        "value" => "foo's label"
                    )
                )
            ),
            $graph->getResourceInformation($resourceUri)
        );
        
        /**
         * check freshly created cache entries
         */
        $propertyQueryId = $this->_fixture->getQueryCache()->generateShortId(
            "SELECT ?p ?o ".
              "FROM <". $this->_testGraphUri ."> ".
             "WHERE {<". $resourceUri ."> ?p ?o.} ".
             "ORDER BY ?p"
        );
        
        $testGraphId = $this->_fixture->getQueryCache()->generateShortId(
            $this->_testGraphUri
        );
                           
        $this->assertEquals(
            array(
                "relatedQueryCacheEntries" => "",
                "graphIds" => array(
                    $this->_fixture->getQueryCache()->generateShortId(
                        $this->_testGraphUri
                    )
                ),
                "query" => "SELECT ?p ?o ".
                             "FROM <". $this->_testGraphUri ."> ".
                            "WHERE {<". $resourceUri ."> ?p ?o.} ".
                            "ORDER BY ?p",
                "result" => array(
                    array(
                        $resourceUri,
                        "http://www.w3.org/2000/01/rdf-schema#label",
                        array(
                            "lang" => null,
                            "datatype" => null,
                            "type" => "literal",
                            "value" => "foo's label"
                        )
                    )
                ),
                "triplePattern" => array(
                    $testGraphId => array(
                        $testGraphId
                        . "_"
                        . $this->_fixture->getQueryCache()->generateShortId($resourceUri, false)
                        . "_*_*"
                    )
                )
            ),
            $this->_fixture->getCache()->get($propertyQueryId)
        ); 
        
        // ---------------------------------------------------------------------
        $this->assertEquals(1, $this->_fixture->getTripleCount($this->_testGraphUri));
        
        /**
         * drop multiple triples
         */
        $this->_fixture->dropMultipleTriples(
            $this->_testGraphUri, $multipleTriples
        );
        // ---------------------------------------------------------------------
        
        $this->assertEquals(0, $this->_fixture->getTripleCount($this->_testGraphUri));
        
        /**
         * check cache entries again
         */
        
        $this->assertFalse($this->_fixture->getCache()->get($propertyQueryId)); 
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
        
        // execute a query to get the content of the graph
        $graph = $this->_fixture->getGraph($this->_testGraphUri);
        $this->assertEquals(
            array(
                array(
                    "s" => "http://s/",
                    "p" => "http://p/",
                    "o" => "http://test/uri"
                )
            ),
            $graph->sparql("SELECT ?s ?p ?o WHERE {?s ?p ?o.}")
        );
    }
    
    public function testDropStmt_focusOnQueryCache()
    {
        $testData = $this->generateTestCacheEntries();
        
        // put test data into the QueryCache
        $this->_fixture->getQueryCache()->rememberQueryResult(
            $testData["query"], $testData["result"]
        );
        
        /**
         * check if everything was created successfully
         */
                
        // graph container
        foreach($testData["graphIds"] as $graphId) {
            $this->assertEquals(
                array($testData["queryId"] => $testData["queryId"]),
                $this->_fixture->getCache()->get($graphId)
            );
        }
        
        // triple pattern
        foreach ($testData["triplePattern"] as $graphId => $triplePattern) {
            foreach ($triplePattern as $pattern) {
                $this->assertEquals(
                    $testData["queryId"], $this->_fixture->getCache()->get($pattern)
                );
            }
        }
        
        // query container
        $this->assertEquals(
            $testData["queryContainer"], $this->_fixture->getCache()->get($testData["queryId"])
        );
        
        /**
         * add a triple which has to lead to an invalidation of the previously
         * created cache data!
         */
        $this->_fixture->dropTriple(
            $this->_testGraphUri, 
            "http://rdfs.org/sioc/ns#foo", 
            "http://predicate/",
            array("type" => "uri", "value" => "http://object")
        );
        
        
        /**
         * check everything again
         */
        
        // graph container
        foreach($testData["graphIds"] as $graphId) {
            $this->assertFalse(
                $this->_fixture->getCache()->get($graphId)
            );
        }
        
        // triple pattern
        foreach ($testData["triplePattern"] as $graphId => $triplePattern) {
            foreach ($triplePattern as $pattern) {
                $this->assertFalse($this->_fixture->getCache()->get($pattern));
            }
        }
        
        // query container
        $this->assertFalse($this->_fixture->getCache()->get($testData["queryId"]));
    }
    
    /**
     * 
     * @param
     * @return
     * @throw
     */
    public function testGetAvailableGraphs()
    {
        $this->_cache->clean();
        
        $this->assertFalse(
            $this->_cache->get("sto". $this->_fixture->getId() ."_availableGraphUris")
        );
        
        $graphUris = $this->_fixture->getAvailableGraphUris();
        
        $this->assertTrue(0 < count($graphUris));
        
        $this->assertEquals(
            $graphUris,
            $this->_cache->get("sto". $this->_fixture->getId() ."_availableGraphUris")
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
        
        $this->assertTrue(0 < $this->_fixture->getTripleCount());
    }
    
    /**
     * function importRdf
     */
     
    public function testImportRdf_usingGraphInstance()
    {
        $turtleString = "<http://model.org/model#localName> 
        a <http://model.org/model#className1>, <http://model.org/model#className2> ;
        <http://www.w3.org/2000/01/rdf-schema#label> 'label1', 'label2'@nl .";
        
        $this->_fixture->getGraph($this->_testGraphUri)->importRdf(
            $turtleString, "turtle"
        );
        
        // graph has to contain 4 new triples
        $this->assertEquals(
            4, 
            $this->_fixture->getGraph($this->_testGraphUri)->getTripleCount()
        );
    }    
     
    public function testImportRdf_newStoreAndGraphInstance()
    {
        $this->_cache->clean();
        
        // TODO find a way to drop that too
        $graphUri = "http://localhost/Enable/importRdfExample/";
        $store = new \Saft\Store($this->_storeConfig, $this->_cache);
        $store->addGraph($graphUri);
        
        $turtleString = "<http://model.org/model#localName> 
        a <http://model.org/model#className1>, <http://model.org/model#className2> ;
        <http://www.w3.org/2000/01/rdf-schema#label> 'label1', 'label2'@nl .";
        
        $this->_cache->clean();
        
        $store->getGraph($graphUri)->importRdf(
            $turtleString, "turtle"
        );
        
        // graph has to contain 4 new triples
        $this->assertEquals(4, $store->getGraph($graphUri)->getTripleCount());
        
        $this->_fixture->dropGraph($graphUri);
    }    
    
    /**
     * 
     * @param
     * @return
     * @throw
     */
    public function testIsGraphAvailable()
    {
        $this->assertFalse($this->_fixture->isGraphAvailable("not existing graph"));
        
        $this->assertTrue($this->_fixture->isGraphAvailable($this->_testGraphUri));
    }    
    
    /**
     * function sparql
     */
    
    public function testSparql()
    {
        $this->assertEquals(0, $this->_fixture->getTripleCount($this->_testGraphUri));
        
        $this->assertEquals(
            array(),
            $this->_fixture->sparql(
                "SELECT ?s ?p ?o 
                   FROM <" . $this->_testGraphUri . "> 
                  WHERE {?s ?p ?o.}"
            )
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
            $this->_fixture->sparql("SELECT ?s ?p ?o FROM <" . $this->_testGraphUri . "> WHERE {?s ?p ?o.}")
        );
        
        $this->assertEquals(
            array(),
            $this->_fixture->sparql(
                "SELECT ?s ?p ?o FROM <" . $this->_testGraphUri . "> WHERE {?s ?p ?o.}", 
                array("resultType" => "array")
            )
        );
    }
    
    public function testSparql_emptyResult()
    {
        $this->_fixture->clearGraph($this->_testGraphUri);
        
        $this->assertEquals(
            array(),
            $this->_fixture->sparql(
                "SELECT ?s ?p ?o FROM <" . $this->_testGraphUri . "> WHERE {?s ?p ?o.}"
            )
        );
    }
    
    public function testSparql_focusOnQueryCache()
    {
        $query = "SELECT ?s ?p ?o FROM <". $this->_testGraphUri ."> WHERE {?s ?p ?o.}";
        $queryId = $this->_fixture->getQueryCache()->generateShortId($query);
        
        // be sure that nothing is in the QueryCache already
        $this->assertFalse($this->_fixture->getCache()->get($queryId));
        
        $this->assertEquals(array(), $this->_fixture->sparql($query));
        
        
        // notice: 
        // at this point, the QueryCache has new entries because of the sparql
        // function call
        
        $testGraphId = $this->_fixture->getQueryCache()->generateShortId($this->_testGraphUri);
        
        $this->assertEquals(
            array(
                "relatedQueryCacheEntries" => "",
                "graphIds" => array(
                    $testGraphId
                ),
                "result" => array(),
                "query" => $query,
                "triplePattern" => array(
                    $testGraphId => array(
                        $testGraphId . "_*_*_*"
                    )
                )
            ),
            $this->_fixture->getCache()->get($queryId)
        );
        
    }
    
    public function testSparql_invalidResultType()
    {
        $this->setExpectedException("\Exception");
        
        $this->_fixture->sparql(
            "SELECT ?s ?p ?o WHERE {?s ?p ?o.}",
            array("resultType" => "invalid")
        );
    }
}
