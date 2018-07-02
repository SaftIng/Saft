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

use Saft\Data\Parser;
use Saft\Data\ParserFactory;
use Saft\Rdf\Test\TestCase;

/**
 * @codeCoverageIgnore
 */
abstract class AbstractParserFactoryTest extends TestCase
{
    /**
     * This list represents all serializations that are supported by the Parsers behind the ParserFactory
     * class to test.
     *
     * @var array
     */
    protected $availableSerializations = [];

    /**
     * @return ParserFactory
     */
    abstract protected function getInstance(): ParserFactory;

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

        $this->fixture = $this->getInstance();

        foreach ($this->availableSerializations as $serialization) {
            $this->assertTrue($this->fixture->createParserFor($serialization) instanceof Parser);
        }
    }

    /**
     * @expectedException \Exception
     */
    public function testCreateParserForRequestInvalidSerialization()
    {
        $this->getInstance()->createParserFor('invalid serialization');
    }
}
