<?php

namespace Saft\Data\Test;

use Saft\Test\TestCase;

abstract class SerializerFactoryAbstractTest extends TestCase
{
    /**
     * This list represents all serializations that are supported by the Serializers behind the ParserFactory
     * class to test.
     *
     * @var array
     */
    protected $availableSerializations = array();

    /**
     * @return SerializerFactory
     */
    abstract protected function newInstance();

    /*
     * Tests for createSerializerFor
     */

    // simple test to go through all availableSerializations and check for each that an object
    // is returned by the SerializerFactory instance.
    public function testCreateSerializerFor()
    {
        $this->fixture = $this->newInstance();

        foreach ($this->fixture->getSupportedSerializations() as $serialization) {
            $this->assertTrue(is_object($this->fixture->createSerializerFor($serialization)));
        }
    }

    public function testCreateSerializerForRequestInvalidSerialization()
    {
        // expected exception because invalid serialization was given
        $this->setExpectedException('\Exception');

        $this->newInstance()->createSerializerFor('invalid serialization');
    }
}
