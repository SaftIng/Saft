<?php

namespace Saft\Data\Test;

use Saft\Data\ParserFactory;
use Saft\Test\TestCase;

abstract class ParserFactoryAbstractTest extends TestCase
{
    /**
     * This list represents all serializations that are supported by the Parsers behind the ParserFactory
     * class to test.
     *
     * @var array
     */
    protected $availableSerializations = array();

    /**
     * @return ParserFactory
     */
    abstract protected function newInstance();

    /*
     * Tests for createParserFor
     */

    // simple test to go through all availableSerializations and check for each that an object
    // is returned by the ParserFactory instance.
    public function testCreateParserFor()
    {
        if (0 == count($this->availableSerializations)) {
            $this->markTestSkipped('Array $availableSerializations contains no entries.');
        }

        $this->fixture = $this->newInstance();

        foreach ($this->availableSerializations as $serialization) {
            $this->assertTrue(is_object($this->fixture->createParserFor($serialization)));
        }
    }

    public function testCreateParserForRequestInvalidSerialization()
    {
        // expected exception because invalid serialization was given
        $this->setExpectedException('\Exception');

        $this->newInstance()->createParserFor('invalid serialization');
    }
}
