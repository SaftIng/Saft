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

use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\RdfHelpers;
use Saft\Rdf\StatementImpl;
use Saft\Rdf\Test\TestCase;

/**
 * @codeCoverageIgnore
 */
abstract class AbstractSerializerTest extends TestCase
{
    /**
     * @param string $serialization
     *
     * @return Serializer
     */
    abstract protected function newInstance($serialization);

    /*
     * Tests for serializeIteratorToStream
     */

    public function testSerializeIteratorToStreamAsNTriplesInvalidOutputStreamHandling()
    {
        try {
            // serialize $iterator to turtle
            $this->fixture = $this->newInstance('n-triples');
        } catch (\Exception $e) {
            $this->markTestSkipped($e->getMessage());
        }

        $this->setExpectedException('Exception');

        $this->fixture->serializeIteratorToStream(
            new ArrayStatementIteratorImpl([]),
            42
        );
    }

    public function testSerializeIteratorToStreamAsNTriplesOutputStreamString()
    {
        try {
            // serialize $iterator to turtle
            $this->fixture = $this->newInstance('n-triples');
        } catch (\Exception $e) {
            $this->markTestSkipped($e->getMessage());
        }

        $iterator = new ArrayStatementIteratorImpl([
            new StatementImpl(
                new NamedNodeImpl('http://saft/example/'),
                new NamedNodeImpl('http://www.w3.org/1999/02/22-rdf-syntax-ns#type'),
                new NamedNodeImpl('http://saft/example/Foo')
            ),
            new StatementImpl(
                new NamedNodeImpl('http://saft/example/2'),
                new NamedNodeImpl('http://www.w3.org/1999/02/22-rdf-syntax-ns#type'),
                new NamedNodeImpl('http://saft/example/Foo')
            ),
        ]);

        $filepath = tempnam(sys_get_temp_dir(), 'saft_');

        $this->fixture->serializeIteratorToStream($iterator, 'file://'.$filepath, 'n-quads');

        // check
        $this->assertEquals(
            '<http://saft/example/> <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> '.
            '<http://saft/example/Foo> .'.PHP_EOL.
            '<http://saft/example/2> <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> '.
            '<http://saft/example/Foo> .',
            trim(file_get_contents($filepath))
        );
    }

    public function testSerializeIteratorToStreamAsNTriples()
    {
        try {
            // serialize $iterator to turtle
            $this->fixture = $this->newInstance('n-triples');

            if (false === in_array('n-triples', $this->fixture->getSupportedSerializations())) {
                $this->markTestSkipped('Fixture does not support n-triples serialization.');
            }
        } catch (\Exception $e) {
            $this->markTestSkipped($e->getMessage());
        }

        $iterator = new ArrayStatementIteratorImpl([
            new StatementImpl(
                new NamedNodeImpl('http://saft/example/'),
                new NamedNodeImpl('http://www.w3.org/1999/02/22-rdf-syntax-ns#type'),
                new NamedNodeImpl('http://saft/example/Foo')
            ),
            new StatementImpl(
                new NamedNodeImpl('http://saft/example/2'),
                new NamedNodeImpl('http://www.w3.org/1999/02/22-rdf-syntax-ns#type'),
                new NamedNodeImpl('http://saft/example/Foo')
            ),
        ]);

        $filepath = tempnam(sys_get_temp_dir(), 'saft_');
        $testFile = fopen('file://'.$filepath, 'w+');

        $this->fixture->serializeIteratorToStream($iterator, $testFile, 'n-triples');

        // check
        $this->assertEquals(
            '<http://saft/example/> <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> '.
            '<http://saft/example/Foo> .'.PHP_EOL.
            '<http://saft/example/2> <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> '.
            '<http://saft/example/Foo> .',
            trim(file_get_contents($filepath))
        );
    }

    /*
     * Tests for setPrefixes
     */

    // purpose of this test is to call setPrefixes method and be sure, it acts as expected.
    public function testSetPrefixes()
    {
        // $this->setExpectedException('\Exception');

        $this->fixture = $this->newInstance('n-triples');
        $this->assertNull($this->fixture->setPrefixes([]));
    }
}
