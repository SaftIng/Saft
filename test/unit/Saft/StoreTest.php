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
                    FROM <" . $this->testGraphUri . ">
                    FROM <" . $this->testGraphUri . "2>
                   WHERE {
                     <http://rdfs.org/sioc/ns#foo> ?p ?o.
                     ?s <http://www.w3.org/2000/01/rdf-schema#label> \"foo\".
                     FILTER (?o < 40)
                   }";
        $result = array("foo" => 1337);

        $queryId = $this->fixture->getQueryCache()->generateShortId($query);
        $this->assertFalse($this->fixture->getCache()->get($queryId));

        /**
         * graph container
         */
        $graphUris = array(
            $this->testGraphUri, $this->testGraphUri . "2"
        );
        $graphIds = array();

        foreach ($graphUris as $graphUri) {
            $graphIds[] = $this->fixture->getQueryCache()->generateShortId($graphUri);
        }

        foreach ($graphIds as $graphId) {
            $this->assertFalse($this->fixture->getCache()->get($graphId));
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
        $sPO = "_". $this->fixture->getQueryCache()->generateShortId("http://rdfs.org/sioc/ns#foo", false)
               . "_*_*";

        $sLabelFoo = "_*_" .
            $this->fixture->getQueryCache()->generateShortId(
                "http://www.w3.org/2000/01/rdf-schema#label",
                false
            ) . "_*";

        foreach ($graphIds as $graphId) {
            $triplePattern[$graphId] = array(
                $graphId . $sPO,
                $graphId . $sLabelFoo
            );

            $this->assertFalse($this->fixture->getCache()->get($graphId . $sPO));
            $this->assertFalse($this->fixture->getCache()->get($graphId . $sLabelFoo));
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

        $this->fixture = new \Saft\Store($this->storeConfig, $this->cache);
        $this->fixture->addGraph($this->testGraphUri);

        $this->cache->clean();
    }

    /**
     *
     */
    public function tearDown()
    {
        $this->fixture->dropGraph($this->testGraphUri);
        $this->fixture->getCache()->clean();

        parent::tearDown();
    }

    /**
     * function addGraph
     */
    public function testAddGraph()
    {
        $this->fixture->dropGraph($this->testGraphUri);

        $this->assertFalse(
            $this->fixture->isGraphAvailable($this->testGraphUri)
        );

        // clear cache
        $this->cache->clean();

        $this->assertFalse(
            $this->cache->get("enable_store_availableGraphs")
        );

        // add graph
        $this->fixture->addGraph($this->testGraphUri);

        $this->assertTrue(
            $this->fixture->isGraphAvailable($this->testGraphUri)
        );

        // check cache for added graph
        $this->assertTrue(
            array_key_exists(
                $this->testGraphUri,
                $this->cache->get("sto". $this->fixture->getId() ."_availableGraphUris")
            )
        );
    }

    /**
     * function addMultipleTriples
     */

    public function testAddMultipleStmts()
    {
        // graph is empty
        $this->assertEquals(0, $this->fixture->getTripleCount($this->testGraphUri));

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
        $this->fixture->addMultipleTriples($this->testGraphUri, $multipleTriples);

        // graph contains two entries
        $this->assertEquals(2, $this->fixture->getTripleCount($this->testGraphUri));

        // execute a query to get the content of the graph
        $graph = $this->fixture->getGraph($this->testGraphUri);
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

    public function testAddMultipleStmtsFocusOnQueryCache()
    {
        $testData = $this->generateTestCacheEntries();

        // put test data into the QueryCache
        $this->fixture->getQueryCache()->rememberQueryResult(
            $testData["query"],
            $testData["result"]
        );

        /**
         * check if everything was created successfully
         */

        // graph container
        foreach ($testData["graphIds"] as $graphId) {
            $this->assertEquals(
                array($testData["queryId"] => $testData["queryId"]),
                $this->fixture->getCache()->get($graphId)
            );
        }

        // triple pattern
        foreach ($testData["triplePattern"] as $graphId => $triplePattern) {
            foreach ($triplePattern as $pattern) {
                $this->assertEquals(
                    $testData["queryId"],
                    $this->fixture->getCache()->get($pattern)
                );
            }
        }

        // query container
        $this->assertEquals(
            $testData["queryContainer"],
            $this->fixture->getCache()->get($testData["queryId"])
        );

        /**
         * add triples which have to lead to an invalidation of the previously
         * created cache entries!
         */
        $this->fixture->addMultipleTriples(
            $this->testGraphUri,
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
        foreach ($testData["graphIds"] as $graphId) {
            $this->assertFalse(
                $this->fixture->getCache()->get($graphId)
            );
        }

        // triple pattern
        foreach ($testData["triplePattern"] as $graphId => $triplePattern) {
            foreach ($triplePattern as $pattern) {
                $this->assertFalse($this->fixture->getCache()->get($pattern));
            }
        }

        // query container
        $this->assertFalse($this->fixture->getCache()->get($testData["queryId"]));
    }

    /**
     * function addTriple
     */

    public function testAddStmt()
    {
        // graph is empty
        $this->assertEquals(0, $this->fixture->getTripleCount($this->testGraphUri));

        // add triple
        $this->fixture->addTriple(
            $this->testGraphUri,
            "http://s/",
            "http://p/",
            array("type" => "uri", "value" => "http://test/uri")
        );

        // graph is empty
        $this->assertEquals(1, $this->fixture->getTripleCount($this->testGraphUri));

        // execute a query to get the content of the graph
        $graph = $this->fixture->getGraph($this->testGraphUri);
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

    public function testAddStmtFocusOnQueryCache()
    {
        $testData = $this->generateTestCacheEntries();

        // put test data into the QueryCache
        $this->fixture->getQueryCache()->rememberQueryResult(
            $testData["query"],
            $testData["result"]
        );

        /**
         * check if everything was created successfully
         */

        // graph container
        foreach ($testData["graphIds"] as $graphId) {
            $this->assertEquals(
                array($testData["queryId"] => $testData["queryId"]),
                $this->fixture->getCache()->get($graphId)
            );
        }

        // triple pattern
        foreach ($testData["triplePattern"] as $graphId => $triplePattern) {
            foreach ($triplePattern as $pattern) {
                $this->assertEquals(
                    $testData["queryId"],
                    $this->fixture->getCache()->get($pattern)
                );
            }
        }

        // query container
        $this->assertEquals(
            $testData["queryContainer"],
            $this->fixture->getCache()->get($testData["queryId"])
        );

        /**
         * add a triple which has to lead to an invalidation of the previously
         * created cache data!
         */
        $this->fixture->addTriple(
            $this->testGraphUri,
            "http://rdfs.org/sioc/ns#foo",
            "http://predicate/",
            array("type" => "uri", "value" => "http://object")
        );


        /**
         * check everything again
         */

        // graph container
        foreach ($testData["graphIds"] as $graphId) {
            $this->assertFalse(
                $this->fixture->getCache()->get($graphId)
            );
        }

        // triple pattern
        foreach ($testData["triplePattern"] as $graphId => $triplePattern) {
            foreach ($triplePattern as $pattern) {
                $this->assertFalse($this->fixture->getCache()->get($pattern));
            }
        }

        // query container
        $this->assertFalse($this->fixture->getCache()->get($testData["queryId"]));
    }

    /**
     * function dropGraph
     */

    public function testDropGraph()
    {
        $this->fixture->dropGraph($this->testGraphUri);

        $this->assertFalse(
            $this->fixture->isGraphAvailable($this->testGraphUri)
        );

        $this->fixture->addGraph($this->testGraphUri);

        $this->assertTrue(
            $this->fixture->isGraphAvailable($this->testGraphUri)
        );

        $this->fixture->dropGraph($this->testGraphUri);

        $this->assertFalse(
            $this->fixture->isGraphAvailable($this->testGraphUri)
        );
    }

    public function testDropGraphFocusOnQueryCache()
    {
        $testData = $this->generateTestCacheEntries();

        // put test data into the QueryCache
        $this->fixture->getQueryCache()->rememberQueryResult(
            $testData["query"],
            $testData["result"]
        );

        /**
         * check if everything was created successfully
         */

        // graph container
        foreach ($testData["graphIds"] as $graphId) {
            $this->assertEquals(
                array($testData["queryId"] => $testData["queryId"]),
                $this->fixture->getCache()->get($graphId)
            );
        }

        // triple pattern
        foreach ($testData["triplePattern"] as $graphId => $triplePattern) {
            foreach ($triplePattern as $pattern) {
                $this->assertEquals(
                    $testData["queryId"],
                    $this->fixture->getCache()->get($pattern)
                );
            }
        }

        // query container
        $this->assertEquals(
            $testData["queryContainer"],
            $this->fixture->getCache()->get($testData["queryId"])
        );


        /**
         * add a triple which has to lead to an invalidation of the previously
         * created cache data!
         */
        $this->fixture->dropGraph($this->testGraphUri);


        /**
         * check everything again
         */

        // graph container
        foreach ($testData["graphIds"] as $graphId) {
            if ($graphId ===
                $this->fixture->getQueryCache()->generateShortId($this->testGraphUri)) {
                $this->assertFalse(
                    $this->fixture->getCache()->get($graphId)
                );
            }
        }

        // triple pattern
        foreach ($testData["triplePattern"] as $graphId => $triplePattern) {
            foreach ($triplePattern as $pattern) {
                $this->assertFalse($this->fixture->getCache()->get($pattern));
            }
        }

        // query container
        $this->assertFalse($this->fixture->getCache()->get($testData["queryId"]));
    }

    /**
     * function dropMultipleTriples
     */

    public function testDropMultipleStmts()
    {
        /**
         * Create some test data
         */
        $this->assertEquals(0, $this->fixture->getTripleCount($this->testGraphUri));

        // 2 triples
        $multipleTriples = array(
            array("http://s/", "http://p/", array("type" => "uri", "value" => "http://test/uri")),
            array("http://s/", "http://p/", array("type" => "literal", "value" => "test literal"))
        );
        $this->fixture->addMultipleTriples($this->testGraphUri, $multipleTriples);
        $this->assertEquals(2, $this->fixture->getTripleCount($this->testGraphUri));

        /**
         * drop multiple triples
         */
        $this->fixture->dropMultipleTriples(
            $this->testGraphUri,
            $multipleTriples
        );

        $this->assertEquals(0, $this->fixture->getTripleCount($this->testGraphUri));
    }

    public function testDropMultipleStmtsFocusOnQueryCache()
    {
        $graph = $this->fixture->getGraph($this->testGraphUri);
        $this->assertEquals(0, $this->fixture->getTripleCount($this->testGraphUri));


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
        $this->fixture->addMultipleTriples($this->testGraphUri, $multipleTriples);

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
                "SELECT ?s ?p ?o WHERE {?s ?p ?o.}",
                array("useQueryCache" => false)
            )
        );

        /**
         * check cache entries; the adding of multiple triples has to lead to
         * an invalidation of a couple of cache entries
         */

        // graph container
        foreach ($testData["graphIds"] as $graphId) {
            $this->assertFalse($this->fixture->getCache()->get($graphId));
        }

        // triple pattern
        foreach ($testData["triplePattern"] as $graphId => $triplePattern) {
            foreach ($triplePattern as $pattern) {
                $this->assertFalse($this->fixture->getCache()->get($pattern));
            }
        }

        // query container
        $this->assertFalse($this->fixture->getCache()->get($testData["queryId"]));

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
        $propertyQueryId = $this->fixture->getQueryCache()->generateShortId(
            "SELECT ?p ?o ".
            "FROM <". $this->testGraphUri ."> ".
            "WHERE {<". $resourceUri ."> ?p ?o.} ".
            "ORDER BY ?p"
        );

        $testGraphId = $this->fixture->getQueryCache()->generateShortId(
            $this->testGraphUri
        );

        $this->assertEquals(
            array(
                "relatedQueryCacheEntries" => "",
                "graphIds" => array(
                    $this->fixture->getQueryCache()->generateShortId(
                        $this->testGraphUri
                    )
                ),
                "query" => "SELECT ?p ?o ".
                             "FROM <". $this->testGraphUri ."> ".
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
                        . $this->fixture->getQueryCache()->generateShortId($resourceUri, false)
                        . "_*_*"
                    )
                )
            ),
            $this->fixture->getCache()->get($propertyQueryId)
        );

        // ---------------------------------------------------------------------
        $this->assertEquals(1, $this->fixture->getTripleCount($this->testGraphUri));

        /**
         * drop multiple triples
         */
        $this->fixture->dropMultipleTriples(
            $this->testGraphUri,
            $multipleTriples
        );
        // ---------------------------------------------------------------------

        $this->assertEquals(0, $this->fixture->getTripleCount($this->testGraphUri));

        /**
         * check cache entries again
         */

        $this->assertFalse($this->fixture->getCache()->get($propertyQueryId));
    }

    /**
     * function dropTriple
     */

    public function testDropStmt()
    {
        /**
         * Create some test data
         */
        $this->assertEquals(0, $this->fixture->getTripleCount($this->testGraphUri));

        // 2 triples
        $multipleTriples = array(
            array("http://s/", "http://p/", array("type" => "uri", "value" => "http://test/uri")),
            array("http://s/", "http://p/", array("type" => "literal", "value" => "test literal"))
        );
        $this->fixture->addMultipleTriples($this->testGraphUri, $multipleTriples);
        $this->assertEquals(2, $this->fixture->getTripleCount($this->testGraphUri));

        /**
         * drop one triple
         */
        $this->fixture->dropTriple(
            $this->testGraphUri,
            "http://s/",
            "http://p/",
            array("type" => "literal", "value" => "test literal")
        );

        $this->assertEquals(1, $this->fixture->getTripleCount($this->testGraphUri));

        // execute a query to get the content of the graph
        $graph = $this->fixture->getGraph($this->testGraphUri);
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

    public function testDropStmtFocusOnQueryCache()
    {
        $testData = $this->generateTestCacheEntries();

        // put test data into the QueryCache
        $this->fixture->getQueryCache()->rememberQueryResult(
            $testData["query"],
            $testData["result"]
        );

        /**
         * check if everything was created successfully
         */

        // graph container
        foreach ($testData["graphIds"] as $graphId) {
            $this->assertEquals(
                array($testData["queryId"] => $testData["queryId"]),
                $this->fixture->getCache()->get($graphId)
            );
        }

        // triple pattern
        foreach ($testData["triplePattern"] as $graphId => $triplePattern) {
            foreach ($triplePattern as $pattern) {
                $this->assertEquals(
                    $testData["queryId"],
                    $this->fixture->getCache()->get($pattern)
                );
            }
        }

        // query container
        $this->assertEquals(
            $testData["queryContainer"],
            $this->fixture->getCache()->get($testData["queryId"])
        );

        /**
         * add a triple which has to lead to an invalidation of the previously
         * created cache data!
         */
        $this->fixture->dropTriple(
            $this->testGraphUri,
            "http://rdfs.org/sioc/ns#foo",
            "http://predicate/",
            array("type" => "uri", "value" => "http://object")
        );


        /**
         * check everything again
         */

        // graph container
        foreach ($testData["graphIds"] as $graphId) {
            $this->assertFalse(
                $this->fixture->getCache()->get($graphId)
            );
        }

        // triple pattern
        foreach ($testData["triplePattern"] as $graphId => $triplePattern) {
            foreach ($triplePattern as $pattern) {
                $this->assertFalse($this->fixture->getCache()->get($pattern));
            }
        }

        // query container
        $this->assertFalse($this->fixture->getCache()->get($testData["queryId"]));
    }

    /**
     *
     * @param
     * @return
     * @throw
     */
    public function testGetAvailableGraphs()
    {
        $this->cache->clean();

        $this->assertFalse(
            $this->cache->get("sto". $this->fixture->getId() ."_availableGraphUris")
        );

        $graphUris = $this->fixture->getAvailableGraphUris();

        $this->assertTrue(0 < count($graphUris));

        $this->assertEquals(
            $graphUris,
            $this->cache->get("sto". $this->fixture->getId() ."_availableGraphUris")
        );
    }

    /**
     * function getTripleCount
     */

    public function testGetTripleCount()
    {
        // graph is empty
        $this->assertEquals(0, $this->fixture->getTripleCount($this->testGraphUri));

        // add 3 triples to the graph
        $this->fixture->addMultipleTriples($this->testGraphUri, array(
            array("http://s/", "http://p/", array("type" => "uri", "value" => "http://o/")),
            array("http://s/", "http://p/", array("type" => "uri", "value" => "http://o/1")),
            array("http://s/", "http://p/", array("type" => "uri", "value" => "http://o/2"))
        ));

        // graph has to contain 3 triples
        $this->assertEquals(3, $this->fixture->getTripleCount($this->testGraphUri));

        $this->assertTrue(0 < $this->fixture->getTripleCount());
    }

    /**
     * function importRdf
     */

    public function testImportRdfUsingGraphInstance()
    {
        $turtleString = "<http://model.org/model#localName>
        a <http://model.org/model#className1>, <http://model.org/model#className2> ;
        <http://www.w3.org/2000/01/rdf-schema#label> 'label1', 'label2'@nl .";

        $this->fixture->getGraph($this->testGraphUri)->importRdf(
            $turtleString,
            "turtle"
        );

        // graph has to contain 4 new triples
        $this->assertEquals(
            4,
            $this->fixture->getGraph($this->testGraphUri)->getTripleCount()
        );
    }

    public function testImportRdfNewStoreAndGraphInstance()
    {
        $this->cache->clean();

        // TODO find a way to drop that too
        $graphUri = "http://localhost/Enable/importRdfExample/";
        $store = new \Saft\Store($this->storeConfig, $this->cache);
        $store->addGraph($graphUri);

        $turtleString = "<http://model.org/model#localName>
        a <http://model.org/model#className1>, <http://model.org/model#className2> ;
        <http://www.w3.org/2000/01/rdf-schema#label> 'label1', 'label2'@nl .";

        $this->cache->clean();

        $store->getGraph($graphUri)->importRdf(
            $turtleString,
            "turtle"
        );

        // graph has to contain 4 new triples
        $this->assertEquals(4, $store->getGraph($graphUri)->getTripleCount());

        $this->fixture->dropGraph($graphUri);
    }

    /**
     *
     * @param
     * @return
     * @throw
     */
    public function testIsGraphAvailable()
    {
        $this->assertFalse($this->fixture->isGraphAvailable("not existing graph"));

        $this->assertTrue($this->fixture->isGraphAvailable($this->testGraphUri));
    }

    /**
     * function sparql
     */

    public function testSparql()
    {
        $this->assertEquals(0, $this->fixture->getTripleCount($this->testGraphUri));

        $this->assertEquals(
            array(),
            $this->fixture->sparql(
                "SELECT ?s ?p ?o
                   FROM <" . $this->testGraphUri . ">
                  WHERE {?s ?p ?o.}"
            )
        );

        // add 3 triples to the graph
        $multipleTriples = array(
            array("http://s/", "http://p/", array("type" => "uri", "value" => "http://o/")),
            array("http://s/", "http://p/", array("type" => "uri", "value" => "http://o/1")),
            array("http://s/", "http://p/", array("type" => "uri", "value" => "http://o/2"))
        );

        $this->fixture->addMultipleTriples($this->testGraphUri, $multipleTriples);

        $this->assertEquals(3, $this->fixture->getTripleCount($this->testGraphUri));

        $this->assertEquals(
            array(array(
                "s" => "http://s/", "p" => "http://p/", "o" => "http://o/"
            ), array(
                "s" => "http://s/", "p" => "http://p/", "o" => "http://o/1"
            ), array(
                "s" => "http://s/", "p" => "http://p/", "o" => "http://o/2"
            )),
            $this->fixture->sparql(
                "SELECT ?s ?p ?o
                   FROM <" . $this->testGraphUri . ">
                  WHERE {?s ?p ?o.}"
            )
        );
    }

    public function testSparqlDifferentResultTypes()
    {
        $this->assertEquals(
            array(),
            $this->fixture->sparql("SELECT ?s ?p ?o FROM <" . $this->testGraphUri . "> WHERE {?s ?p ?o.}")
        );

        $this->assertEquals(
            array(),
            $this->fixture->sparql(
                "SELECT ?s ?p ?o FROM <" . $this->testGraphUri . "> WHERE {?s ?p ?o.}",
                array("resultType" => "array")
            )
        );
    }

    public function testSparqlEmptyResult()
    {
        $this->fixture->clearGraph($this->testGraphUri);

        $this->assertEquals(
            array(),
            $this->fixture->sparql(
                "SELECT ?s ?p ?o FROM <" . $this->testGraphUri . "> WHERE {?s ?p ?o.}"
            )
        );
    }

    public function testSparqlFocusOnQueryCache()
    {
        $query = "SELECT ?s ?p ?o FROM <". $this->testGraphUri ."> WHERE {?s ?p ?o.}";
        $queryId = $this->fixture->getQueryCache()->generateShortId($query);

        // be sure that nothing is in the QueryCache already
        $this->assertFalse($this->fixture->getCache()->get($queryId));

        $this->assertEquals(array(), $this->fixture->sparql($query));


        // notice:
        // at this point, the QueryCache has new entries because of the sparql
        // function call

        $testGraphId = $this->fixture->getQueryCache()->generateShortId($this->testGraphUri);

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
            $this->fixture->getCache()->get($queryId)
        );

    }

    public function testSparqlInvalidResultType()
    {
        $this->setExpectedException("\Exception");

        $this->fixture->sparql(
            "SELECT ?s ?p ?o WHERE {?s ?p ?o.}",
            array("resultType" => "invalid")
        );
    }
}
