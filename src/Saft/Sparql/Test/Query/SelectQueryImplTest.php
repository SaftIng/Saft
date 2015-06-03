<?php

namespace Saft\Sparql\Test\Query;

use Saft\Rdf\NamedNodeImpl;
use Saft\Sparql\Query\SelectQueryImpl;
use Saft\Test\TestCase;

class SelectQueryImplTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->fixture = new SelectQueryImpl();
    }

    /*
     * Tests for constructor
     */

    public function testConstructor()
    {
        $this->fixture = new SelectQueryImpl(
            'SELECT ?s ?p ?o FROM <'. $this->testGraph->getUri() .'> WHERE {?s ?p ?o.}'
        );

        $queryParts = $this->fixture->getQueryParts();

        // select
        $this->assertEquals('SELECT ?s ?p ?o', $queryParts['select']);

        // from
        $this->assertEquals(array($this->testGraph->getUri()), $queryParts['graphs']);

        // where
        $this->assertEquals('WHERE {?s ?p ?o.}', $queryParts['where']);
    }

    public function testConstructorNotAllVariablesUsed()
    {
        $instanceToCheckAgainst = new SelectQueryImpl(
            'SELECT ?x FROM <'. $this->testGraph->getUri() .'> WHERE {?x ?y ?z}'
        );

        $this->assertEquals(
            $instanceToCheckAgainst,
            new SelectQueryImpl('SELECT ?x FROM <'. $this->testGraph->getUri() .'> WHERE {?x ?y ?z}')
        );
    }

    public function testConstructorWithLimit()
    {
        $this->fixture = new SelectQueryImpl(
            'SELECT ?s ?p ?o FROM <'. $this->testGraph->getUri() .'> WHERE {?s ?p ?o.} LIMIT 10'
        );

        $queryParts = $this->fixture->getQueryParts();

        // limit
        $this->assertEquals('10', $queryParts['limit']);
    }

    public function testConstructorWithOffset()
    {
        $this->fixture = new SelectQueryImpl(
            'SELECT ?s ?p ?o FROM <'. $this->testGraph->getUri() .'> WHERE {?s ?p ?o.} Offset 5'
        );

        $queryParts = $this->fixture->getQueryParts();

        // offset
        $this->assertEquals('5', $queryParts['offset']);
    }

    public function testConstructorWithLimitOffset()
    {
        $this->fixture = new SelectQueryImpl(
            'SELECT ?s ?p ?o FROM <'. $this->testGraph->getUri() .'> WHERE {?s ?p ?o.} LIMIT 10 OFFSET 5'
        );

        $queryParts = $this->fixture->getQueryParts();

        // select
        $this->assertEquals('SELECT ?s ?p ?o', $queryParts['select']);

        // from
        $this->assertEquals(array($this->testGraph->getUri()), $queryParts['graphs']);

        // where
        $this->assertEquals('WHERE {?s ?p ?o.}', $queryParts['where']);

        // limit
        $this->assertEquals('10', $queryParts['limit']);

        // offset
        $this->assertEquals('5', $queryParts['offset']);
    }

    /*
     * Tests for getQueryParts
     */

    public function testGetQueryParts()
    {
        $this->fixture = new SelectQueryImpl(
            'PREFIX foo: <http://bar.de/>
             SELECT ?s ?p ?o
               FROM <http://foo/bar/>
               FROM NAMED <http://foo/bar/named>
              WHERE {
                    ?s ?p ?o.
                    ?s?p?o.
                    ?s <http://www.w3.org/2000/01/rdf-schema#label> "Foo"^^<http://www.w3.org/2001/XMLSchema#string> .
                    ?s ?foo "val EN"@en
                    FILTER (?o = "Bar")
                    FILTER (?o > 40)
                    FILTER regex(?g, "r", "i")
               }
              LIMIT 10
             OFFSET 5 '
        );

        $queryParts = $this->fixture->getQueryParts();

        // Checks the return for the following patterns:
        // FILTER (?o = 'Bar')
        // FILTER (?o > 40)
        // FILTER regex(?g, 'r', 'i')
        $this->assertEquals(
            array(
                array(
                    'type'      => 'expression',
                    'sub_type'  => 'relational',
                    'patterns'  => array(
                        array(
                            'value'     => 'o',
                            'type'      => 'var',
                            'operator'  => ''
                        ),
                        array(
                            'value'     => 'Bar',
                            'type'      => 'literal',
                            'sub_type'  => 'literal2',
                            'operator'  => ''
                        )
                    ),
                    'operator'  => '='
                ),

                // FILTER (?o > 40)
                array(
                    'type'      => 'expression',
                    'sub_type'  => 'relational',
                    'patterns'  => array(
                        array(
                            'value'     => 'o',
                            'type'      => 'var',
                            'operator'  => ''
                        ),
                        array(
                            'value'     => '40',
                            'type'      => 'literal',
                            'operator'  => '',
                            'datatype'  => 'http://www.w3.org/2001/XMLSchema#integer'
                        )
                    ),
                    'operator'  => '>'
                ),

                // FILTER regex(?g, 'r', 'i')
                array(
                    'args' => array(
                        array(
                            'value' => 'g',
                            'type' => 'var',
                            'operator' => ''
                        ),
                        array(
                            'value' => 'r',
                            'type' => 'literal',
                            'sub_type' => 'literal2',
                            'operator' => ''
                        ),
                        array(
                            'value' => 'i',
                            'type' => 'literal',
                            'sub_type' => 'literal2',
                            'operator' => ''
                        ),
                    ),
                    'type' => 'built_in_call',
                    'call' => 'regex',
                ),
            ),
            $queryParts['filter_pattern']
        );

        // graphs
        $this->assertEquals(array('http://foo/bar/'), $queryParts['graphs']);

        // named graphs
        $this->assertEquals(array('http://foo/bar/named'), $queryParts['named_graphs']);

        // triple patterns
        // Checks the return for the following patterns:
        // ?s ?p ?o.
        // ?s?p?o.
        // ?s <http://www.w3.org/2000/01/rdf-schema#label> \'Foo\'^^<http://www.w3.org/2001/XMLSchema#string> .
        // ?s ?foo \'val EN\'@en .
        $this->assertEquals(
            array(
                // ?s ?p ?o.
                array(
                    's'             => 's',
                    'p'             => 'p',
                    'o'             => 'o',
                    's_type'        => 'var',
                    'p_type'        => 'var',
                    'o_type'        => 'var',
                    'o_datatype'    => '',
                    'o_lang'        => ''
                ),
                // ?s?p?o.
                array(
                    's'             => 's',
                    'p'             => 'p',
                    'o'             => 'o',
                    's_type'        => 'var',
                    'p_type'        => 'var',
                    'o_type'        => 'var',
                    'o_datatype'    => '',
                    'o_lang'        => ''
                ),
                // ?s <http://www.w3.org/2000/01/rdf-schema#label>
                //    \'Foo\'^^<http://www.w3.org/2001/XMLSchema#string> .
                array(
                    's'             => 's',
                    'p'             => 'http://www.w3.org/2000/01/rdf-schema#label',
                    'o'             => 'Foo',
                    's_type'        => 'var',
                    'p_type'        => 'uri',
                    'o_type'        => 'typed-literal',
                    'o_datatype'    => 'http://www.w3.org/2001/XMLSchema#string',
                    'o_lang'        => ''
                ),
                // ?s ?foo \'val EN\'@en .
                array(
                    's'             => 's',
                    'p'             => 'foo',
                    'o'             => 'val EN',
                    's_type'        => 'var',
                    'p_type'        => 'var',
                    'o_type'        => 'literal',
                    'o_datatype'    => '',
                    'o_lang'        => 'en'
                )
            ),
            $queryParts['triple_pattern']
        );

        /**
         * limit
         */
        $this->assertEquals('10', $queryParts['limit']);

        /**
         * offset
         */
        $this->assertEquals('5', $queryParts['offset']);

        /**
         * prefixes
         */
        $this->assertEquals(
            array(
                'rdfs' => 'http://www.w3.org/2000/01/rdf-schema#',
                'xsd'  => 'http://www.w3.org/2001/XMLSchema#'
            ),
            $queryParts['namespaces']
        );

        /**
         * prefixes
         */
        $this->assertEquals(array('foo' => 'http://bar.de/'), $queryParts['prefixes']);

        /**
         * result vars
         */
        $this->assertEquals(array('s', 'p', 'o'), $queryParts['result_variables']);

        /**
         * variables
         */
        $this->assertEquals(array('s', 'p', 'o', 'foo', 'g'), $queryParts['variables']);
    }

    /*
     * Tests for isAskQuery
     */

    public function testIsAskQuery()
    {
        $this->assertFalse($this->fixture->isAskQuery());
    }

    /*
     * Tests for isDescribeQuery
     */

    public function testIsDescribeQuery()
    {
        $this->assertFalse($this->fixture->isDescribeQuery());
    }

    /*
     * Tests for isGraphQuery
     */

    public function testIsGraphQuery()
    {
        $this->assertFalse($this->fixture->isGraphQuery());
    }

    /*
     * Tests for isSelectQuery
     */

    public function testIsSelectQuery()
    {
        $this->assertTrue($this->fixture->isSelectQuery());
    }

    /*
     * Tests for isUpdateQuery
     */

    public function testIsUpdateQuery()
    {
        $this->assertFalse($this->fixture->isUpdateQuery());
    }
}
