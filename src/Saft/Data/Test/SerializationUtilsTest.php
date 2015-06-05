<?php

namespace Saft\Data\Test;

use Saft\Data\SerializationUtils;
use Saft\Test\TestCase;

class SerializationUtilsTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->fixture = new SerializationUtils();
    }

    /*
     * Tests for mimeToSerialization
     */

    public function testMimeToSerialization()
    {
        $mimeTypeToSerializationMap = array(
            'application/json' => array('rdf-json', 'json-ld'),
            'application/n-quads' => 'n-quads',
            'application/n-triples' => 'n-triples',
            'application/rdf+xml' => 'rdf-xml',
            'text/html' => 'rdfa',
            'application/trig' => 'trig',
            'text/turtle' => 'turtle'
        );

        // go through the map and check that a given MIME type results in the expected
        // serialization.
        foreach ($mimeTypeToSerializationMap as $mime => $serialization) {
            if (is_array($serialization)) {
                $this->assertTrue(
                    in_array($this->fixture->mimeToSerialization($mime), $serialization),
                    'For MIME-type: '. $mime
                );

            } else {
                $this->assertEquals(
                    $serialization,
                    $this->fixture->mimeToSerialization($mime),
                    'For MIME-type: '. $mime
                );
            }
        }
    }

    /*
     * Tests for serializationToMime
     */

    public function testSerializationToMime()
    {
        $serializationMimeTypeMap = array(
            'json-ld' => 'application/json',
            'n-quads' => 'application/n-quads',
            'n-triples' => 'application/n-triples',
            'rdf-json' => 'application/json',
            'rdf-xml' => 'application/rdf+xml',
            'rdfa' => 'text/html',
            'trig' => 'application/trig',
            'turtle' => 'text/turtle'
        );

        // go through the map and check that a given $serialization results in the expected
        // MIME type.
        foreach ($serializationMimeTypeMap as $serialization => $mime) {
            $this->assertEquals(
                $mime,
                $this->fixture->serializationToMime($serialization),
                'For serialization: '. $serialization
            );
        }
    }
}
