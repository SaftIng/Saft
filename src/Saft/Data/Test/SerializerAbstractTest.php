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
     * Tests serializeIteratorToStream
     */
    public function testSerializeIteratorToStream()
    {
        $iterator = new ArrayStatementIteratorImpl(array(
            new StatementImpl(
                new NamedNodeImpl('http://saft/example/'),
                new NamedNodeImpl('http://www.w3.org/1999/02/22-rdf-syntax-ns#type'),
                new NamedNodeImpl('http://saft/example/Foo')
            ),
        ));

        $testFile = sys_get_temp_dir() .'/saft/serialize.ttl';

        // serialize $iterator to turtle
        $this->fixture = $this->newInstance();
        $this->fixture->serializeIteratorToStream($iterator, $testFile, 'turtle');

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
            '<http://saft/example/Foo> .'. PHP_EOL,
            $string
        );
    }
}
