<?php

namespace Saft\Data;

use Saft\Rdf\StatementIterator;

interface Serializer
{
    /**
     * Set the prefixes which the serializer can/should use when generating the serialization.
     * Please keep in mind, that some serializations don't support prefixes at all or that some
     * implementations might ignore them.
     *
     * @param array $prefixes An associative array with a prefix mapping of the prefixes. The key
     *                        will be the prefix, while the values contains the according namespace URI.
     */
    public function setPrefixes(array $prefixes);

    /**
     * Transforms the statements of a StatementIterator instance into a stream, a file for instance.
     *
     * @param  StatementIterator $statements   The StatementIterator containing all the Statements which
     *                                         should be serialized by the serializer.
     * @param  string            $outputStream filename of the stream to where the serialization should be
     *                                         written.
     * @param  string            $format       The serialization which should be used. If null is given the
     *                                         serializer will either apply some default serialization, or
     *                                         the only one it is supporting, or will throw an Exception.
     * @throws \Exception If unknown format was given.
     */
    public function serializeIteratorToStream(StatementIterator $statements, $outputStream, $format = null);

    /**
     * Returns a list of all supported serialization types.
     *
     * @return array Array of supported serialization types which can be used by this serializer.
     */
    public function getSupportedSerializations();
}
