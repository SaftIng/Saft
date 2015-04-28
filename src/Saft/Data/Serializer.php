<?php

namespace Saft\Data;

use Saft\Rdf\StatementIterator;

interface SerializerInterface
{
    /**
     * Set the prefixes which the serializer can/should use when generating the serialization. Please keep in mind, that
     * some serializations don't support prefixes at all or that some implementations might ignore them.
     * @param $prefixes array An associative array with a prefix mapping of the prefixes. The key will be the prefix,
     *                        while the values contains the according namespace URI.
     * @return void
     */
    public function setPrefixes(array $prefixes);

    /**
     * @unstable
     * @param $outputStream string filename of the stream to where the serialization should be written
     *                             {@url http://php.net/manual/en/book.stream.php}
     * @param $statements StatementIterator The StatementIterator containing all the Statements which should be
     *                                      serialized by the serializer.
     * @param $serialization string the serialization which should be used. If null is given the serializer will either
     *                              apply some default serialization, or the only one it is supporting, or will throw an
     *                              Exception.
     * @return void
     */
    public function serializeIteratorToStream($outputStream, StatementIterator $statements, $serialization = null);

    /**
     * @unstable
     * @return array of supported mime types which can be used by this serializer
     */
    public function getSupportedSerializations();
}
