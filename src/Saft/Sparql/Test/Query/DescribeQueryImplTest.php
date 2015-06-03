<?php

namespace Saft\Sparql\Test\Query;

use Saft\Sparql\Query\DescribeQueryImpl;
use Saft\Test\TestCase;

class DescribeQueryImplTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->fixture = new DescribeQueryImpl();
    }

    /*
     * Tests for constructor
     */

    public function testConstructor()
    {
        $this->fixture = new DescribeQueryImpl(
            'PREFIX foaf: <http://xmlns.com/foaf/0.1/>
            DESCRIBE ?x
            FROM <http://foobar/>
            WHERE { ?x foaf:name "Alice" }'
        );

        $this->assertEquals(
            'PREFIX foaf: <http://xmlns.com/foaf/0.1/>
            DESCRIBE ?x
            FROM <http://foobar/>
            WHERE { ?x foaf:name "Alice" }',
            $this->fixture->getQuery()
        );
    }


    /*
     * Tests for extractNamespacesFromQuery
     */

    public function testExtractNamespacesFromQuery()
    {
        $this->fixture = new DescribeQueryImpl(
            'PREFIX foaf: <http://xmlns.com/foaf/0.1/>
            DESCRIBE ?x
            FROM <http://foo/bar/>
            WHERE { ?x <http://foobar/name> "Alice". ?y <http://www.w3.org/2001/XMLSchema#string> "Alice". }'
        );

        $queryParts = $this->fixture->getQueryParts();

        $this->assertEquals(
            array('ns-0' => 'http://foobar/', 'xsd' => 'http://www.w3.org/2001/XMLSchema#'),
            $queryParts['namespaces']
        );
    }

    public function testExtractNamespacesFromQueryNoNamespaces()
    {
        $this->fixture = new DescribeQueryImpl(
            'PREFIX foaf: <http://xmlns.com/foaf/0.1/>
            DESCRIBE ?x
            FROM <http://foobar/>
            WHERE { ?x foaf:name "Alice" }'
        );

        $queryParts = $this->fixture->getQueryParts();

        $this->assertFalse(isset($queryParts['namespaces']));
    }

    /*
     * Tests for extractPrefixesFromQuery
     */

    public function testExtractPrefixesFromQuery()
    {
        // assumption here is that fixture is of type
        $this->fixture = new DescribeQueryImpl(
            'PREFIX foaf: <http://xmlns.com/foaf/0.1/>
            DESCRIBE ?x
            FROM <http://foo/bar/>
            WHERE { ?x <http://foobar/name> "Alice" }'
        );

        $queryParts = $this->fixture->getQueryParts();

        $this->assertEquals(
            array('foaf' => 'http://xmlns.com/foaf/0.1/'),
            $queryParts['prefixes']
        );
    }

    public function testExtractPrefixesFromQueryNoPrefixes()
    {
        // assumption here is that fixture is of type
        $this->fixture = new DescribeQueryImpl(
            'DESCRIBE ?x
            FROM <http://foo/bar/>
            WHERE { ?x <http://foobar/name> "Alice" }'
        );

        $queryParts = $this->fixture->getQueryParts();

        $this->assertFalse(isset($queryParts['prefixes']));
    }

    /*
     * Tests for getQueryParts
     */

    public function testGetQueryPartsEverything()
    {
        $this->fixture = new DescribeQueryImpl(
            'PREFIX foaf: <http://xmlns.com/foaf/0.1/>
            DESCRIBE ?s
            FROM <http://foo/bar/>
            FROM NAMED <http://foo/bar/named>
            WHERE { ?s ?p ?o. FILTER (?o < 40) }'
        );

        $queryParts = $this->fixture->getQueryParts();

        $this->assertEquals(8, count($queryParts));

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
        $this->assertEquals(array('http://foo/bar/'), $queryParts['graphs']);
        $this->assertEquals(array('http://foo/bar/named'), $queryParts['named_graphs']);
        $this->assertEquals(array('foaf' => 'http://xmlns.com/foaf/0.1/'), $queryParts['prefixes']);
        $this->assertEquals(array('s'), $queryParts['result_variables']);
        $this->assertEquals('describeWhere', $queryParts['sub_type']);
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
        $this->assertTrue($this->fixture->isDescribeQuery());
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
