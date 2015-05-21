<?php

namespace Saft\Sparql\Test\Query;

use Saft\Rdf\NamedNodeImpl;
use Saft\Sparql\Query\GraphQueryImpl;
use Saft\Test\TestCase;

class GraphQueryImplTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->fixture = new GraphQueryImpl();
    }

    /**
     * Tests constructor
     */

    public function testConstructor()
    {
        $this->fixture = new GraphQueryImpl('CREATE GRAPH <'. $this->testGraph->getUri() .'>');

        $this->assertEquals('CREATE GRAPH <'. $this->testGraph->getUri() .'>', $this->fixture->getQuery());
    }

    /**
     * Tests extractFilterPattern
     */

    public function testExtractFilterPattern()
    {
        $this->assertEquals(array(), $this->fixture->extractFilterPattern(''));
    }

    /**
     * Tests extractNamespacesFromQuery
     */

    public function testExtractNamespacesFromQuery()
    {
        $this->assertEquals(array(), $this->fixture->extractNamespacesFromQuery(''));
    }

    /**
     * Tests extractPrefixesFromQuery
     */

    public function testExtractPrefixesFromQuery()
    {
        $this->assertEquals(array(), $this->fixture->extractPrefixesFromQuery(''));
    }

    /**
     * Tests extractTriplePattern
     */

    public function testExtractTriplePattern()
    {
        $this->assertEquals(array(), $this->fixture->extractTriplePattern(''));
    }

    /**
     * Tests extractVariablesFromQuery
     */

    public function testExtractVariablesFromQuery()
    {
        $this->assertEquals(array(), $this->fixture->extractVariablesFromQuery(''));
    }

    /**
     * Tests getQueryParts
     */

    public function testGetQueryParts()
    {
        $this->fixture->init('CLEAR GRAPH <'. $this->testGraph->getUri() .'>');

        $queryParts = $this->fixture->getQueryParts();

        $this->assertEquals(2, count($queryParts));
        $this->assertEquals(array($this->testGraph->getUri()), $queryParts['graphs']);
        $this->assertEquals('clearGraph', $queryParts['sub_type']);
    }

    /**
     * Tests determineSubType
     */

    public function testDetermineSubType()
    {
        $this->assertEquals(
            'clearGraph',
            $this->fixture->determineSubType('CLEAR GRAPH <'. $this->testGraph->getUri() .'>')
        );

        $this->assertEquals(
            'createGraph',
            $this->fixture->determineSubType('CREATE GRAPH <'. $this->testGraph->getUri() .'>')
        );

        $this->assertEquals(
            'createSilentGraph',
            $this->fixture->determineSubType('CREATE SILENT GRAPH <'. $this->testGraph->getUri() .'>')
        );

        $this->assertEquals(
            'dropGraph',
            $this->fixture->determineSubType('DROP GRAPH <'. $this->testGraph->getUri() .'>')
        );

        $this->assertEquals(
            'dropSilentGraph',
            $this->fixture->determineSubType('DROP SILENT GRAPH <'. $this->testGraph->getUri() .'>')
        );
    }

    public function testDetermineSubTypeUnknownType()
    {
        $this->assertNull($this->fixture->determineSubType('unknown type'));
    }

    /**
     * Tests init
     */

    public function testInit()
    {
        $this->fixture = new GraphQueryImpl();
        $this->fixture->init('CREATE GRAPH <'. $this->testGraph->getUri() .'>');

        $this->assertEquals('CREATE GRAPH <'. $this->testGraph->getUri() .'>', $this->fixture->getQuery());
    }

    /**
     * Tests isAskQuery
     */

    public function testIsAskQuery()
    {
        $this->assertFalse($this->fixture->isAskQuery());
    }

    /**
     * Tests isDescribeQuery
     */

    public function testIsDescribeQuery()
    {
        $this->assertFalse($this->fixture->isDescribeQuery());
    }

    /**
     * Tests isGraphQuery
     */

    public function testIsGraphQuery()
    {
        $this->assertTrue($this->fixture->isGraphQuery());
    }

    /**
     * Tests isSelectQuery
     */

    public function testIsSelectQuery()
    {
        $this->assertFalse($this->fixture->isSelectQuery());
    }

    /**
     * Tests isUpdateQuery
     */

    public function testIsUpdateQuery()
    {
        $this->assertFalse($this->fixture->isUpdateQuery());
    }
}
