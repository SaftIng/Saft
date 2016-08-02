<?php

namespace Saft\Skeleton\Test\Integration\Data;

use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\StatementFactoryImpl;
use Saft\Skeleton\Data\SerializerFactory;
use Saft\Skeleton\Test\TestCase;

class SerializerFactoryTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->fixture = new SerializerFactory(new NodeFactoryImpl(), new StatementFactoryImpl());
    }

    /*
     * Tests for createParserFor
     */

    public function testCreateParserFor()
    {
        $serializationMap = array('n-triples', 'rdf-json', 'rdf-xml', 'rdfa', 'turtle');

        foreach ($serializationMap as $serialization) {
            $this->assertTrue(is_object($this->fixture->createSerializerFor($serialization)));
        }
    }

    public function testCreateParserForInvalidSerialization()
    {
        $this->assertNull($this->fixture->createSerializerFor('invalid'));
    }
}
