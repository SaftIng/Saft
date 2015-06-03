<?php

namespace Saft\Sparql\Test\Query;

use Saft\Sparql\Query\AskQueryImpl;
use Saft\Test\TestCase;

class AskQueryImplTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->fixture = new AskQueryImpl();
    }

    /*
     * Tests for constructor
     */

    public function testConstructor()
    {
        $this->fixture = new AskQueryImpl('ASK {?s ?p ?o.}');

        $queryParts = $this->fixture->getQueryParts();

        $this->assertEquals('?s ?p ?o.', $queryParts['where']);
    }

    public function testConstructorNoWherePart()
    {
        $this->setExpectedException('\Exception');

        $this->fixture = new AskQueryImpl('ASK {?s ?p ?o.');
    }

    /*
     * Tests for extractNamespacesFromQuery
     */

    public function testExtractNamespacesFromQuery2()
    {
        $this->fixture = new AskQueryImpl(
            'PREFIX foo: <http://bar.de> ASK WHERE {
                ?s <http://foobar/hey> ?o. ?s <http://foobar/ho> ?o. ?s <http://www.w3.org/2001/XMLSchema#> ?o
             }'
        );

        $queryParts = $this->fixture->getQueryParts();

        $this->assertEquals(
            array('ns-0' => 'http://foobar/', 'xsd' => 'http://www.w3.org/2001/XMLSchema#'),
            $queryParts['namespaces']
        );
    }

    public function testExtractNamespacesFromQueryNoNamespaces()
    {
        $this->fixture = new AskQueryImpl('ASK WHERE { ?s ?p ?o }');

        $queryParts = $this->fixture->getQueryParts();

        $this->assertFalse(isset($queryParts['namespaces']));
    }

    /*
     * Tests for extractPrefixesFromQuery
     */

    public function testExtractPrefixesFromQuery()
    {
        // assumption here is that fixture is of type
        $this->fixture = new AskQueryImpl(
            'PREFIX foo: <http://bar.de> ASK { ?s ?p ?o }'
        );

        $queryParts = $this->fixture->getQueryParts();

        $this->assertEquals(array('foo' => 'http://bar.de'), $queryParts['prefixes']);
    }

    public function testExtractPrefixesFromQueryNoPrefixes()
    {
        // assumption here is that fixture is of type
        $this->fixture = new AskQueryImpl(
            'ASK WHERE { ?s ?p ?o }'
        );

        $queryParts = $this->fixture->getQueryParts();

        $this->assertFalse(isset($queryParts['prefixes']));
    }

    /*
     * Tests for getQueryParts
     */

    public function testGetQueryPartsEverything()
    {
        $this->fixture = new AskQueryImpl(
            'PREFIX foo: <http://bar.de> ASK FROM <http://foobar/> { ?s ?p ?o. FILTER (?o < 40) }'
        );

        $queryParts = $this->fixture->getQueryParts();

        $this->assertEquals(6, count($queryParts));

        $this->assertEquals(
            array(
                array(
                    'type' => 'expression',
                    'sub_type' => 'relational',
                    'patterns' => array(
                        array(
                            'value' => 'o',
                            'type' => 'var',
                            'operator' => ''
                        ),
                        array(
                            'value' => '40',
                            'type' => 'literal',
                            'operator' => '',
                            'datatype' => 'http://www.w3.org/2001/XMLSchema#integer'
                        ),
                    ),
                    'operator' => '<'
                )
            ),
            $queryParts['filter_pattern']
        );
        $this->assertEquals(array('http://foobar/'), $queryParts['graphs']);
        $this->assertEquals(array('foo' => 'http://bar.de'), $queryParts['prefixes']);
        $this->assertEquals(
            array(
                array(
                    's' => 's',
                    'p' => 'p',
                    'o' => 'o',
                    's_type' => 'var',
                    'p_type' => 'var',
                    'o_type' => 'var',
                    'o_datatype' => null,
                    'o_lang' => null
                )
            ),
            $queryParts['triple_pattern']
        );
        $this->assertEquals(array('s', 'p', 'o'), $queryParts['variables']);
        $this->assertEquals('?s ?p ?o. FILTER (?o < 40)', $queryParts['where']);
    }

    public function testGetQueryPartsWithPrefixesTriplePatternVariables()
    {
        $this->fixture = new AskQueryImpl('PREFIX foo: <http://bar.de> ASK { ?s ?p ?o }');

        $queryParts = $this->fixture->getQueryParts();

        $this->assertEquals(4, count($queryParts));

        $this->assertEquals(array('foo' => 'http://bar.de'), $queryParts['prefixes']);
        $this->assertEquals(
            array(
                array(
                    's' => 's',
                    'p' => 'p',
                    'o' => 'o',
                    's_type' => 'var',
                    'p_type' => 'var',
                    'o_type' => 'var',
                    'o_datatype' => null,
                    'o_lang' => null
                )
            ),
            $queryParts['triple_pattern']
        );
        $this->assertEquals(array('s', 'p', 'o'), $queryParts['variables']);
        $this->assertEquals('?s ?p ?o', $queryParts['where']);
    }

    /*
     * Tests for isAskQuery
     */

    public function testIsAskQuery()
    {
        $this->assertTrue($this->fixture->isAskQuery());
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
        $this->assertFalse($this->fixture->isSelectQuery());
    }

    /*
     * Tests for isUpdateQuery
     */

    public function testIsUpdateQuery()
    {
        $this->assertFalse($this->fixture->isUpdateQuery());
    }
}
