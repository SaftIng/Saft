<?php

namespace Saft\Sparql\Test;

use \Saft\TestCase;
use \Saft\Sparql\Query;

class QueryUnitTest extends TestCase
{
    public function setUp()
    {
        $this->fixture = new Query();
    }

    /**
     * Tests getQueryParts
     */

    public function testGetQueryParts()
    {
        $this->fixture->init(
            'SELECT ?s ?p ?o
               FROM <http://foo/bar/1>
               FROM NAMED <http://foo/bar/2>
              WHERE {
                    ?s ?p ?o.
                    ?s?p?o.
                    ?s <http://www.w3.org/2000/01/rdf-schema#label> "Foo"^^<http://www.w3.org/2001/XMLSchema#string> .
                    ?s ?foo "val EN"@en .
                    FILTER (?o = "Bar")
                    FILTER (?o > 40)
                    FILTER regex(?g, "r", "i")
               }
              LIMIT 10
             OFFSET 5 '
        );

        /**
         Remaining filter clauses to cover:
         - FILTER (?decimal * 10 > ?minPercent )
         - FILTER (isURI(?person) && !bound(?person))
         - FILTER (lang(?title) = 'en')
         - FILTER regex(?ssn, '...')
        */

        $queryParts = $this->fixture->getQueryParts();

        /**
         * variables
         */
        $this->assertEquals(
            array(
                's', 'p', 'o', 'foo', 'g'
            ),
            $queryParts['vars']
        );

        /**
         * prefixes
         */
        $this->assertEquals(
            array(
                'rdfs:' => 'http://www.w3.org/2000/01/rdf-schema#',
                'xsd:'  => 'http://www.w3.org/2001/XMLSchema#'
            ),
            $queryParts['prefixes']
        );

        /**
         * query
         */

        // type
        $this->assertEquals(
            'select',
            $queryParts['query']['type']
        );

        // result vars
        $this->assertEquals(
            array(
                array('value' => 's', 'type' => 'var'),
                array('value' => 'p', 'type' => 'var'),
                array('value' => 'o', 'type' => 'var')
            ),
            $queryParts['query']['result_vars']
        );

        // dataset
        $this->assertEquals(
            array(
                array('graph' => 'http://foo/bar/1', 'named' => 0),
                array('graph' => 'http://foo/bar/2', 'named' => 1)
            ),
            $queryParts['query']['dataset']
        );

        /**
         * query -> pattern
         */

        // type
        $this->assertEquals(
            'group',
            $queryParts['query']['pattern']['type']
        );

        // patterns
        $this->assertEquals(
            array(
                // Checks the return for the following patterns:
                // ?s ?p ?o.
                // ?s?p?o.
                // ?s <http://www.w3.org/2000/01/rdf-schema#label> \'Foo\'^^<http://www.w3.org/2001/XMLSchema#string> .
                // ?s ?foo \'val EN\'@en .
                array(
                    'type'      => 'triples',
                    'patterns'  => array(
                        // ?s ?p ?o.
                        array(
                            'type'          => 'triple',
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
                            'type'          => 'triple',
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
                            'type'          => 'triple',
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
                            'type'          => 'triple',
                            's'             => 's',
                            'p'             => 'foo',
                            'o'             => 'val EN',
                            's_type'        => 'var',
                            'p_type'        => 'var',
                            'o_type'        => 'literal',
                            'o_datatype'    => '',
                            'o_lang'        => 'en'
                        )
                    )
                ),

                // FILTER (?s = 'Bar')
                array(
                    'type' => 'filter',
                    'constraint' => array(
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
                    )
                ),

                // FILTER (?o > 40)
                array(
                    'type' => 'filter',
                    'constraint' => array(
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
                    )
                ),

                // FILTER regex(?g, 'r', 'i')
                array(
                    'constraint' => array(
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
                    'type' => 'filter'
                ),
            ),
            $queryParts['query']['pattern']['patterns']
        );

        // limit
        $this->assertEquals(
            '10',
            $queryParts['query']['limit']
        );

        // offset
        $this->assertEquals(
            '5',
            $queryParts['query']['offset']
        );
    }

    /**
     * Tests getType
     */
    
    public function testGetTypeAsk()
    {
        $query = new Query('ASK { ?s ?p ?o }');
        $this->assertEquals('ask', $query->getType());
    }
    
    public function testGetTypeSelectFromWhere()
    {
        $query = new Query('SELECT * FROM <http://s/> WHERE { ?s ?p ?o }');
        $this->assertEquals('select', $query->getType());
    }

    /**
     * Tests getTriplePattern
     */
    public function testGetTriplePattern()
    {
        $this->fixture->init(
            'SELECT ?s ?p ?o
               FROM <'. $this->testGraphUri .'>
              WHERE {
                ?s ?p ?o.
                ?s <http://www.w3.org/2000/01/rdf-schema#label> "foo"@en.
              }'
        );

        $this->assertEquals(
            array(
                // ?s ?p ?o
                array(
                    'type'          => 'triple',
                    's'             => 's',
                    'p'             => 'p',
                    'o'             => 'o',
                    's_type'        => 'var',
                    'p_type'        => 'var',
                    'o_type'        => 'var',
                    'o_datatype'    => '',
                    'o_lang'        => ''
                ),
                // ?s <http://www.w3.org/2000/01/rdf-schema#label> 'foo'@en
                array(
                    'type'          => 'triple',
                    's'             => 's',
                    'p'             => 'http://www.w3.org/2000/01/rdf-schema#label',
                    'o'             => 'foo',
                    's_type'        => 'var',
                    'p_type'        => 'uri',
                    'o_type'        => 'literal',
                    'o_datatype'    => '',
                    'o_lang'        => 'en'
                )
            ),
            $this->fixture->getTriplePatterns()
        );
    }

    /**
     * Tests initFromString
     */

    public function testInitAskQuery()
    {
        $query = new Query('ASK { ?s ?p ?o }');
        
        $this->assertEquals('?s ?p ?o ', $query->getProloguePart());
    }
    
    public function testInitEmptyQueryParameter()
    {
        $this->setExpectedException('\Exception');
        
        $query = new Query();
        $query->init(null);
    }

    public function testInitLangInSelect()
    {
        $simpleQuery = new Query(
            'SELECT ?s ?p ?o (LANG(?o)) as ?oLang FROM <'. $this->testGraphUri .'> WHERE {?s ?p ?o.} LIMIT 10 OFFSET 5'
        );

        // select / prologue part
        $this->assertEquals('SELECT ?s ?p ?o (LANG(?o)) as ?oLang ', $simpleQuery->getSelect());
        $this->assertEquals('SELECT ?s ?p ?o (LANG(?o)) as ?oLang ', $simpleQuery->getProloguePart());

        // from
        $this->assertEquals(
            array($this->testGraphUri),
            $simpleQuery->getFrom()
        );

        // where
        $this->assertEquals(
            'WHERE {?s ?p ?o.}',
            $simpleQuery->getWhere()
        );

        // limit
        $this->assertEquals(
            '10',
            $simpleQuery->getLimit()
        );

        // offset
        $this->assertEquals(
            '5',
            $simpleQuery->getOffset()
        );
    }
    
    public function testInitSelectFromWhereLimitOffset()
    {
        $simpleQuery = new Query(
            'SELECT ?s ?p ?o FROM <'. $this->testGraphUri .'> WHERE {?s ?p ?o.} LIMIT 10 OFFSET 5'
        );

        // select / prologue part
        $this->assertEquals('SELECT ?s ?p ?o ', $simpleQuery->getSelect());
        $this->assertEquals('SELECT ?s ?p ?o ', $simpleQuery->getProloguePart());

        // from
        $this->assertEquals(
            array($this->testGraphUri),
            $simpleQuery->getFrom()
        );

        // where
        $this->assertEquals(
            'WHERE {?s ?p ?o.}',
            $simpleQuery->getWhere()
        );

        // limit
        $this->assertEquals(
            '10',
            $simpleQuery->getLimit()
        );

        // offset
        $this->assertEquals(
            '5',
            $simpleQuery->getOffset()
        );
    }
}
