<?php

namespace Saft\Skeleton\Test\Integration\Data;

use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\NodeUtils;
use Saft\Rdf\StatementFactoryImpl;
use Saft\Rdf\StatementIteratorFactoryImpl;
use Saft\Skeleton\Data\ParserFactory;
use Saft\Skeleton\Test\TestCase;

class ParserFactoryTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->fixture = new ParserFactory(
            new NodeFactoryImpl(new NodeUtils()),
            new StatementFactoryImpl(),
            new StatementIteratorFactoryImpl(),
            new NodeUtils()
        );
    }

    /*
     * Tests for createParserFor
     */

    public function testCreateParserFor()
    {
        $serializationMap = array('n-triples', 'n-quads', 'rdf-json', 'rdf-xml', 'rdfa', 'turtle');

        foreach ($serializationMap as $serialization) {
            $this->assertTrue(is_object($this->fixture->createParserFor($serialization)));
        }
    }

    public function testCreateParserForInvalidSerialization()
    {
        $this->assertNull($this->fixture->createParserFor('invalid'));
    }
}
