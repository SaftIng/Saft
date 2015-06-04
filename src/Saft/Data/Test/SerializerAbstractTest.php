<?php

namespace Saft\Data\Test;

use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\StatementImpl;
use Saft\Test\TestCase;
use Streamer\Stream;

abstract class SerializerAbstractTest extends TestCase
{
    /**
     * @return Serializer
     */
    abstract protected function newInstance();

    /*
     * Tests for serializeIteratorToStream
     */

    public function testSerializeIteratorToStreamAsNQuads()
    {
        // serialize $iterator to turtle
        $this->fixture = $this->newInstance();

        if (false === in_array('n-quads', $this->fixture->getSupportedSerializations())) {
            // Fixture does not support n-quads serialization.
            return;
        }

        $iterator = new ArrayStatementIteratorImpl(array(
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
        ));

        $testFile = sys_get_temp_dir() .'/saft/serialize.ttl';

        $this->fixture->serializeIteratorToStream($iterator, $testFile, 'n-quads');

        // read written data to check them
        $stream = new Stream(fopen($testFile, 'r'));
        $string = '';
        while (!$stream->isEOF()) {
            $string .= $stream->read();
        }

        unlink($testFile);

        // check
        $this->assertEquals(
            '<http://saft/example/> <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> '.
            '<http://saft/example/Foo> .'. PHP_EOL .
            '<http://saft/example/2> <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> '.
            '<http://saft/example/Foo> .',
            trim($string)
        );
    }

    public function testSerializeIteratorToStreamAsNTriples()
    {
        // serialize $iterator to turtle
        $this->fixture = $this->newInstance();

        if (false === in_array('n-triples', $this->fixture->getSupportedSerializations())) {
            $this->markTestSkipped('Fixture does not support n-triples serialization.');
        }

        $iterator = new ArrayStatementIteratorImpl(array(
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
        ));

        $testFile = sys_get_temp_dir() .'/saft/serialize.ttl';

        $this->fixture->serializeIteratorToStream($iterator, $testFile, 'n-triples');

        // read written data to check them
        $stream = new Stream(fopen($testFile, 'r'));
        $string = '';
        while (!$stream->isEOF()) {
            $string .= $stream->read();
        }

        unlink($testFile);

        // check
        $this->assertEquals(
            '<http://saft/example/> <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> '.
            '<http://saft/example/Foo> .'. PHP_EOL .
            '<http://saft/example/2> <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> '.
            '<http://saft/example/Foo> .',
            trim($string)
        );
    }
}
