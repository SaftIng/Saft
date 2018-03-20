<?php

/*
 * This file is part of Saft.
 *
 * (c) Konrad Abicht <hi@inspirito.de>
 * (c) Natanael Arndt <arndt@informatik.uni-leipzig.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Saft\Sparql\Test\Query;

use Saft\Rdf\RdfHelpers;
use Saft\Rdf\Test\TestCase;
use Saft\Sparql\Query\GraphQueryImpl;

class GraphQueryImplTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->fixture = new GraphQueryImpl(null, new RdfHelpers());
    }

    /*
     * Tests for constructor
     */

    public function testConstructor()
    {
        $this->fixture = new GraphQueryImpl('CREATE GRAPH <'.$this->testGraph->getUri().'>', new RdfHelpers());

        $this->assertEquals('CREATE GRAPH <'.$this->testGraph->getUri().'>', $this->fixture->getQuery());
    }

    /*
     * Tests for extractFilterPattern
     */

    public function testExtractFilterPattern()
    {
        $this->assertEquals([], $this->fixture->extractFilterPattern(''));
    }

    /*
     * Tests for extractNamespacesFromQuery
     */

    public function testExtractNamespacesFromQuery()
    {
        $this->assertEquals([], $this->fixture->extractNamespacesFromQuery(''));
    }

    /*
     * Tests for extractPrefixesFromQuery
     */

    public function testExtractPrefixesFromQuery()
    {
        $this->assertEquals([], $this->fixture->extractPrefixesFromQuery(''));
    }

    /*
     * Tests for extractTriplePattern
     */

    public function testExtractTriplePattern()
    {
        $this->assertEquals([], $this->fixture->extractTriplePattern(''));
    }

    /*
     * Tests for extractVariablesFromQuery
     */

    public function testExtractVariablesFromQuery()
    {
        $this->assertEquals([], $this->fixture->extractVariablesFromQuery(''));
    }

    /*
     * Tests for getQueryParts
     */

    public function testGetQueryParts()
    {
        $this->fixture = new GraphQueryImpl('CLEAR GRAPH <'.$this->testGraph->getUri().'>', new RdfHelpers());

        $queryParts = $this->fixture->getQueryParts();

        $this->assertEquals(2, count($queryParts));
        $this->assertEquals([$this->testGraph->getUri()], $queryParts['graphs']);
        $this->assertEquals('clearGraph', $queryParts['sub_type']);
    }

    /*
     * Tests for determineSubType
     */

    public function testDetermineSubType()
    {
        $this->assertEquals(
            'clearGraph',
            $this->fixture->determineSubType('CLEAR GRAPH <'.$this->testGraph->getUri().'>')
        );

        $this->assertEquals(
            'createGraph',
            $this->fixture->determineSubType('CREATE GRAPH <'.$this->testGraph->getUri().'>')
        );

        $this->assertEquals(
            'createSilentGraph',
            $this->fixture->determineSubType('CREATE SILENT GRAPH <'.$this->testGraph->getUri().'>')
        );

        $this->assertEquals(
            'dropGraph',
            $this->fixture->determineSubType('DROP GRAPH <'.$this->testGraph->getUri().'>')
        );

        $this->assertEquals(
            'dropSilentGraph',
            $this->fixture->determineSubType('DROP SILENT GRAPH <'.$this->testGraph->getUri().'>')
        );
    }

    public function testDetermineSubTypeUnknownType()
    {
        $this->assertNull($this->fixture->determineSubType('unknown type'));
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
        $this->assertTrue($this->fixture->isGraphQuery());
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
