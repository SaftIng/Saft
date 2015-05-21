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
     * Tests createSerializerFor
     */

    // simple test to go through all availableSerializations and check for each that an object
    // is returned by the SerializerFactory instance.
    public function testCreateSerializerFor()
    {
        if (0 == count($this->availableSerializations) || empty($this->availableSerializations)) {
            $this->markTestSkipped('Array $availableSerializations contains no entries.');
        }

        $this->fixture = $this->newInstance();

        foreach ($this->availableSerializations as $serialization) {
            $this->assertTrue(is_object($this->fixture->createSerializerFor($serialization)));
        }
    }
}
