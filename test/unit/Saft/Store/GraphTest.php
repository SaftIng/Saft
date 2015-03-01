<?php

namespace Saft\Store;

class GraphTest extends \Saft\TestCase
{
    public function setUp()
    {
        parent::setUp();

        $store = new \Saft\Store($this->storeConfig, $this->cache);
        $store->addGraph($this->testGraphUri);

        $this->fixture = new \Saft\Store\Graph($store, $this->testGraphUri, $this->cache);
    }

    /**
     *
     */
    public function tearDown()
    {
        $this->fixture->getStore()->dropGraph($this->testGraphUri);

        parent::tearDown();
    }

    /**
     * function addMultipleTriples
     */

    public function testAddMultipleTriples()
    {
        $this->fixture->getStore()->dropGraph($this->testGraphUri);
        $this->fixture->getStore()->addGraph($this->testGraphUri);

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
        $this->fixture->addMultipleTriples($multipleTriples);

        // graph is empty
        $this->assertEquals(2, $this->fixture->getTripleCount($this->testGraphUri));

        $this->assertEquals(
            array(
                array(
                    "s" => "http://s/",
                    "p" => "http://p/",
                    "o" => "http://test/uri"
                ),
                array(
                    "s" => "http://s/",
                    "p" => "http://p/",
                    "o" => "test literal"
                )
            ),
            $this->fixture->sparql("SELECT ?s ?p ?o WHERE {?s ?p ?o.}")
        );
    }

    /**
     * function addTriple
     */

    public function testAddTriple()
    {
        // graph is empty
        $this->assertEquals(0, $this->fixture->getTripleCount($this->testGraphUri));

        // add triple
        $this->fixture->addTriple(
            "http://s/",
            "http://p/",
            array("type" => "uri", "value" => "http://test/uri")
        );

        // graph has exactly one entry
        $this->assertEquals(1, $this->fixture->getTripleCount($this->testGraphUri));

        // ask graph for all his entries and check them if they are really the same
        // as meant to be
        $query = "SELECT ?s ?p ?o WHERE {?s ?p ?o.}";
        $this->assertEquals(
            array(
                array(
                    "s" => "http://s/",
                    "p" => "http://p/",
                    "o" => "http://test/uri"
                )
            ),
            $this->fixture->sparql($query)
        );
    }

    public function testAddTripleMultilineStringValue()
    {
        // graph is empty
        $this->assertEquals(0, $this->fixture->getTripleCount($this->testGraphUri));

        $value = "
        Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy \n
        eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam
        voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet
        clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit
        amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam \n\r
        nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat,
        sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum.
        Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor
        sit amet.
        ";

        // add triple
        $this->fixture->addTriple(
            "http://s/",
            "http://p/",
            array("type" => "literal", "value" => $value)
        );

        // graph has exactly one entry
        $this->assertEquals(1, $this->fixture->getTripleCount($this->testGraphUri));

        // ask graph for all his entries and check them if they are really the same
        // as meant to be
        $query = "SELECT ?s ?p ?o WHERE {?s ?p ?o.}";
        $this->assertEquals(
            array(
                array(
                    "s" => "http://s/",
                    "p" => "http://p/",
                    "o" => $value
                )
            ),
            $this->fixture->sparql($query)
        );
    }

    /**
     * function dropMultipleTriples
     */

    public function testDropMultipleTriples()
    {
        /**
         * Create some test data
         */
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
        $this->fixture->addMultipleTriples($multipleTriples);
        $this->assertEquals(2, $this->fixture->getTripleCount($this->testGraphUri));

        /**
         * drop one triple
         */
        $this->fixture->dropMultipleTriples(
            $multipleTriples
        );

        $this->assertEquals(0, $this->fixture->getTripleCount($this->testGraphUri));
    }

    /**
     * function dropTriple
     */

    public function testDropTriple()
    {
        /**
         * Create some test data
         */
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
        $this->fixture->addMultipleTriples($multipleTriples);
        $this->assertEquals(2, $this->fixture->getTripleCount($this->testGraphUri));

        /**
         * drop one triple
         */
        $this->fixture->dropTriple(
            "http://s/",
            "http://p/",
            array("type" => "literal", "value" => "test literal")
        );

        $this->assertEquals(1, $this->fixture->getTripleCount($this->testGraphUri));
    }

    /**
     * function getResourceInformation
     */

    public function testGetResourceInformation()
    {
        /**
         * Generate query id and check that there is no according cache entry.
         */
        $resourceUri = "http://s/";
        $query = "SELECT ?p ?o ".
                   "FROM <". $this->testGraphUri ."> ".
                  "WHERE {<". $resourceUri ."> ?p ?o.} ".
                  "ORDER BY ?p";
        $queryId = $this->fixture->getStore()->getQueryCache()->generateShortId($query);

        // check that cache is empty
        $this->assertEquals(false, $this->cache->get($queryId));

        /**
         * add test triples
         */
        $multipleTriples = array(
            array(
                $resourceUri,
                "http://p/",
                array(
                    "datatype" => null,
                    "lang" => null,
                    "type" => "uri",
                    "value" => "http://test/uri"
                ),
            ),
            array(
                $resourceUri,
                "http://p/1",
                array(
                    "datatype" => "http://www.w3.org/2001/XMLSchema#string",
                    "lang" => null,
                    "type" => "typed-literal",
                    "value" => "test literal"
                )
            ),
            array(
                $resourceUri,
                "http://p/2",
                array(
                    "datatype" => null,
                    "lang" => "en",
                    "type" => "literal",
                    "value" => "val EN"
                )
            )
        );

        /**
         * the add of multiple new triples for the resource has to lead to an
         * invalidation of the created query cache entries from before!
         */
        $this->fixture->addMultipleTriples($multipleTriples);

        // the call of Graph->getResource will query the database and get fresh
        // result
        $this->assertEqualsArrays(
            array(
                array(
                    $resourceUri,
                    "http://p/",
                    array(
                        "datatype" => null,
                        "lang" => null,
                        "type" => "uri",
                        "value" => "http://test/uri"
                    ),
                ),
                array(
                    $resourceUri,
                    "http://p/1",
                    array(
                        "datatype" => "http://www.w3.org/2001/XMLSchema#string",
                        "lang" => null,
                        "type" => "typed-literal",
                        "value" => "test literal"
                    )
                ),
                array(
                    $resourceUri,
                    "http://p/2",
                    array(
                        "datatype" => null,
                        "lang" => null,
                        "type" => "literal",
                        "value" => "val EN"
                    )
                )
            ),
            $this->fixture->getResourceInformation($resourceUri)
        );

        /**
         * check if the cache itself contains the created resource, to be sure
         * that no query was send.
         */
        $cacheEntry = $this->cache->get($queryId);

        $this->assertEqualsArrays(
            array(
                array(
                    $resourceUri,
                    "http://p/",
                    array(
                        "datatype" => null,
                        "lang" => null,
                        "type" => "uri",
                        "value" => "http://test/uri"
                    ),
                ),
                array(
                    $resourceUri,
                    "http://p/1",
                    array(
                        "datatype" => "http://www.w3.org/2001/XMLSchema#string",
                        "lang" => null,
                        "type" => "typed-literal",
                        "value" => "test literal"
                    )
                ),
                array(
                    $resourceUri,
                    "http://p/2",
                    array(
                        "datatype" => null,
                        "lang" => null,
                        "type" => "literal",
                        "value" => "val EN"
                    )
                )
            ),
            $cacheEntry["result"]
        );
    }

    public function testGetResourceInvalidUri()
    {
        $this->setExpectedException("\Exception");

        $this->fixture->getResourceInformation("invalid");
    }

    /**
     * function getTripleCount
     */

    public function testGetStmtCount()
    {
        // graph is empty
        $this->assertEquals(0, $this->fixture->getTripleCount($this->testGraphUri));

        // add 3 triples to the graph
        $this->fixture->addMultipleTriples(array(
            array("http://s/", "http://p/", array("type" => "uri", "value" => "http://o/")),
            array("http://s/", "http://p/", array("type" => "uri", "value" => "http://o/1")),
            array("http://s/", "http://p/", array("type" => "uri", "value" => "http://o/2")),
        ));

        // graph has to contain 3 triples
        $this->assertEquals(3, $this->fixture->getTripleCount());

        $this->assertTrue(0 < $this->fixture->getTripleCount());
    }

    /**
     * function importRdf
     */

    public function testImportRdf()
    {
        $turtleString = "<http://model.org/model#localName>
        a <http://model.org/model#className1>, <http://model.org/model#className2> ;
        <http://www.w3.org/2000/01/rdf-schema#label> \"label1\", \"label2\"@nl .";

        $this->fixture->importRdf($turtleString, "turtle");

        // graph has to contain 4 new triples
        $this->assertEquals(4, $this->fixture->getTripleCount());
    }

    /**
     * instanciation
     */

    public function testInstanciation()
    {
        $store = new \Saft\Store($this->storeConfig, $this->cache);
        $graph = new \Saft\Store\Graph($store, $this->testGraphUri, $this->cache);
    }

    /**
     * function sparql
     */

    public function testSparqlEmptyResult()
    {
        $this->assertEquals(
            array(),
            $this->fixture->sparql("SELECT ?s ?p ?o WHERE {?s ?p ?o.}")
        );
    }

    public function testSparqlDifferentResultTypes()
    {
        $this->assertEquals(
            array(),
            $this->fixture->sparql("SELECT ?s ?p ?o WHERE {?s ?p ?o.}")
        );

        $this->assertEquals(
            array(),
            $this->fixture->sparql(
                "SELECT ?s ?p ?o WHERE {?s ?p ?o.}",
                array("resultType" => "array")
            )
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
