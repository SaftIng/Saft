<?php
namespace Saft\Store;

class HttpTest extends TestCase
{
    /**
     *
     */
    public function setUp()
    {
        parent::setUp();

        // if httpConfig array is set
        if (true === isset($this->config['httpConfig'])) {
            $this->fixture = new \Saft\Store\Virtuoso($this->config['httpConfig']);

        // if standard store is set to http
        } elseif ('http' === $this->config['configuration']['standardStore']['type']) {
            $this->fixture = new \Saft\Store\Virtuoso(
                $this->config['configuration']['standardStore']
            );

        // no configuration is available, dont execute tests
        } else {
            $this->markTestSkipped(
                "Array virtuosoConfig is not set in the config.yml."
            );
        }
    }

    /**
     *
     */
    public function tearDown()
    {
        $this->fixture->dropGraph($this->testGraphUri);

        parent::tearDown();
    }

    /**
     * function addMultipleTriples
     */

    public function testAddMultipleStmts()
    {
        $this->fixture->dropGraph($this->testGraphUri);
        $this->fixture->addGraph($this->testGraphUri);

        // graph is empty
        $this->assertEquals(0, $this->fixture->getTripleCount($this->testGraphUri));

        // 2 triples
        $multipleTriples = array(
            array("http://s/", "http://p/", array("type" => "uri", "value" => "http://test/uri")),
            array("http://s/", "http://p/", array("type" => "literal", "value" => "test literal"))
        );

        // add triples
        $this->fixture->addMultipleTriples($this->testGraphUri, $multipleTriples);

        // graph has two entries
        $this->assertEquals(2, $this->fixture->getTripleCount($this->testGraphUri));
    }

    public function testAddMultipleStmtsLanguageTags()
    {
        // graph is empty
        $this->assertEquals(0, $this->fixture->getTripleCount($this->testGraphUri));

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
        $this->fixture->addMultipleTriples($this->testGraphUri, $multipleTriples);

        // graph has two entries
        $this->assertEquals(2, $this->fixture->getTripleCount($this->testGraphUri));
    }

    public function testAddMultipleStmtsMassiveStmtArray()
    {
        // graph is empty
        $this->assertEquals(0, $this->fixture->getTripleCount($this->testGraphUri));

        // create a huge amount of triples
        $multipleTriples = array();

        for ($i = 0; $i < 10000; ++$i) {
            $multipleTriples[] = array(
                "http://s/$i",
                "http://p/",
                array("type" => "uri", "value" => "http://test/uri")
            );
        }

        // add created triples
        $this->fixture->addMultipleTriples($this->testGraphUri, $multipleTriples);

        // graph has two entries
        $this->assertEquals(10000, $this->fixture->getTripleCount($this->testGraphUri));
    }

    /**
     * function addGraph
     */

    public function testAddGraph()
    {
        /**
         * To test addGraph via HTTP store adapter with Virtuoso behind, you have to:
         * - go System Admin > User Accounts > click Edit by SPARQL user
         * - by "Account Roles" move SPARQL_UPDATE to the right
         * - click Save
         *
         * Now you should be able to send SPARQL Update queries to the public web
         * interface http://localhost:8890/sparql.
         */

        // TODO
    }

    /**
     * function addStmt
     */

    public function testAddStmt()
    {
        // graph is empty
        $this->assertEquals(0, $this->fixture->getTripleCount($this->testGraphUri));

        // add 1 triple
        $this->fixture->addTriple(
            $this->testGraphUri,
            "http://s/",
            "http://p/",
            array("lang" => "en", "datatype" => null, "type" => "literal", "value" => "val EN")
        );

        $this->assertEquals(1, $this->fixture->getTripleCount($this->testGraphUri));
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
         * drop all triples
         */
        $this->fixture->dropMultipleTriples(
            $this->testGraphUri,
            $multipleTriples
        );

        $this->assertEquals(0, $this->fixture->getTripleCount($this->testGraphUri));
    }

    /**
     * Tests dropTriple
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
    }

    /**
     * Tests executeQuery
     */
    public function testExecuteQuery()
    {
        $this->fixture->addGraph($this->testGraphUri);

        $resourceUri = "http://s/";

        /**
         * add 4 test triples
         */
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
            )
        );

        $this->fixture->addMultipleTriples($this->testGraphUri, $multipleTriples);

        $this->assertEqualsArrays(
            array(
                array(
                    "p" => "http://p/",
                    "o" => "val EN",
                    "olang" => "en"
                ),
                array(
                    "p" => "http://p/",
                    "o" => "val DE",
                    "olang" => ""
                ),
                array(
                    "p" => "http://p/",
                    "o" => "1337",
                    "olang" => ""
                ),
            ),
            $this->fixture->executeQuery(
                "SELECT ?p ?o (LANG(?o)) as ?olang WHERE {<". $resourceUri ."> ?p ?o.}",
                "sparql"
            )
        );
    }

    /**
     * Tests existence (simple)
     */
    public function testExistence()
    {
        $this->assertTrue(class_exists("\Saft\Store\Adapter\Http"));
    }

    /**
     * Tests getAvailableGraphUris
     */

    public function testGetAvailableGraphUris()
    {
        // assumption here is that the SPARQL endpoint contains at least one graph.

        $this->assertTrue(0 <$this->fixture->getAvailableGraphUris());
    }

    /**
     * Tests getTripleCount
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
    }

    /**
     * Tests sparqlSelect
     */

    public function testSparqlSelect()
    {
        $this->fixture->addGraph($this->testGraphUri);

        // check that graph is empty
        $this->assertEquals(0, $this->fixture->getTripleCount($this->testGraphUri));

        // add 3 triples to the graph
        $this->fixture->addMultipleTriples($this->testGraphUri, array(
            array("http://s/", "http://p/", array("type" => "uri", "value" => "http://o/")),
            array("http://s/", "http://p/", array("type" => "uri", "value" => "http://o/1")),
            array("http://s/", "http://p/", array("type" => "literal", "value" => "test"))
        ));

        // check that 3 triples were created
        $this->assertEquals(3, $this->fixture->getTripleCount($this->testGraphUri));

        // query triples
        $this->assertEquals(
            array(array(
                "s" => "http://s/", "p" => "http://p/", "o" => "http://o/"
            ), array(
                "s" => "http://s/", "p" => "http://p/", "o" => "http://o/1"
            ), array(
                "s" => "http://s/", "p" => "http://p/", "o" => "test"
            )),
            $this->fixture->sparqlSelect(
                "SELECT ?s ?p ?o
                   FROM <" .$this->testGraphUri . ">
                  WHERE {?s ?p ?o.}"
            )
        );
    }

    public function testSparqlDifferentResultTypes()
    {
        $this->assertEquals(
            array(),
            $this->fixture->sparqlSelect(
                "SELECT ?s ?p ?o FROM <" . $this->testGraphUri . "> WHERE {?s ?p ?o.}"
            )
        );

        $this->assertEquals(
            array(),
            $this->fixture->sparqlSelect(
                "SELECT ?s ?p ?o FROM <" . $this->testGraphUri . "> WHERE {?s ?p ?o.}",
                array("resultType" => "array")
            )
        );
    }

    public function testSparqlEmptyResult()
    {
        //$this->fixture->dropGraph($this->testGraphUri);

        $this->assertEquals(
            array(),
            $this->fixture->sparqlSelect(
                "SELECT ?s ?p ?o FROM <" . $this->testGraphUri . "> WHERE {?s ?p ?o.}"
            )
        );
    }

    public function testSparqlExtendedResult()
    {
        $this->assertEquals(0, $this->fixture->getTripleCount($this->testGraphUri));

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
        $this->fixture->addMultipleTriples($this->testGraphUri, $multipleTriples);

        $this->assertEquals(
            array(
                "head" => array(
                    "link" => array(),
                    "vars" => array("s", "p", "o", "saftLang")
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
                                "value" => "http://p/2"
                            ),
                            "o" => array(
                                "datatype"  => "http://www.w3.org/2001/XMLSchema#string",
                                "type"      => "typed-literal",
                                "value"     => "false"
                            ),
                            "saftLang" => array(
                                "type"      => "literal",
                                "value"     => ""
                            ),
                        ),
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
                                "value"     => "false",
                            ),
                            "saftLang" => array(
                                "type"      => "literal",
                                "value"     => ""
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
                                "datatype"  => "http://www.w3.org/2001/XMLSchema#integer",
                                // there is NO language set! > "lang" => "de",
                                "type"      => "typed-literal",
                                "value"     => "1337"
                            ),
                            "saftLang" => array(
                                "type"      => "literal",
                                "value"     => ""
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
                                "value" => "http://p/"
                            ),
                            "o" => array(
                                "xml:lang"  => "en",
                                "type"      => "literal",
                                "value"     => "val EN"
                            ),
                            "saftLang" => array(
                                "type"      => "literal",
                                "value"     => "en"
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
                                "value" => "http://p/"
                            ),
                            "o" => array(
                                "datatype"  => "http://www.w3.org/2001/XMLSchema#string",
                                "type"      => "typed-literal",
                                "value"     => "val DE"
                            ),
                            "saftLang" => array(
                                "type"      => "literal",
                                "value"     => ""
                            )
                        )
                    )
                )
            ),
            $this->fixture->sparqlSelect(
                "SELECT ?s ?p ?o (LANG(?o)) as ?saftLang
                   FROM <" . $this->testGraphUri . ">
                  WHERE {?s ?p ?o.}",
                array("resultType" => "extended")
            )
        );
    }

    public function testSparqlInvalidQuery()
    {
        $this->setExpectedException("\Exception");

        $this->fixture->sparqlSelect("invalid SPARQL query");
    }

    public function testSparqlInvalidResultType()
    {
        $this->setExpectedException("\Exception");

        $this->fixture->sparqlSelect(
            "SELECT ?s ?p ?o FROM <" . $this->testGraphUri . "> WHERE {?s ?p ?o.}",
            array("resultType" => "invalid")
        );
    }
}
