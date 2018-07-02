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

namespace Saft\Data\Test;

use Saft\Data\Serializer;
use Saft\Rdf\Test\TestCase;

abstract class AbstractSerializerFactoryTest extends TestCase
{
    /**
     * This list represents all serializations that are supported by the Serializers behind the ParserFactory
     * class to test.
     *
     * @var array
     */
    protected $availableSerializations = [];

    /**
     * @return SerializerFactory
     */
    abstract protected function getInstance(): Serializer;

    /*
     * Tests for createSerializerFor
     */

    // simple test to go through all availableSerializations and check for each that an object
    // is returned by the SerializerFactory instance.
    public function testCreateSerializerFor()
    {
        $this->fixture = $this->getInstance();

        foreach ($this->fixture->getSupportedSerializations() as $serialization) {
            $this->assertTrue($this->fixture->createSerializerFor($serialization) instanceof Serializer);
        }
    }

    /**
     * @expectedException \Exception
     */
    public function testCreateSerializerForRequestInvalidSerialization()
    {
        $this->getInstance()->createSerializerFor('invalid serialization');
    }
}
