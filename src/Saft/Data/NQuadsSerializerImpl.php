<?php

namespace Saft\Data;

use Saft\Rdf\StatementIterator;

class NQuadsSerializerImpl implements Serializer
{
    public function __construct($serialization)
    {
        if ('n-quads' != $serialization && 'n-triples' != $serialization) {
            throw new \Exception(
                'Unknown format given: '. $serialization .'. This serializer only supports n-quads and n-triples.'
            );
        }

        $this->serialization = $serialization;
    }

    /**
     * Set the prefixes which the serializer can/should use when generating the serialization.
     * Prefixes are ignored here.
     *
     * @param array $prefixes An associative array with a prefix mapping of the prefixes. The key
     *                        will be the prefix, while the values contains the according namespace URI.
     */
    public function setPrefixes(array $prefixes)
    {
    }

    /**
     * Transforms the statements of a StatementIterator instance into a stream, a file for instance.
     *
     * @param StatementIterator $statements   The StatementIterator containing all the Statements which
     *                                        should be serialized by the serializer.
     * @param string|resource   $outputStream Filename or file pointer to the stream to where the serialization
     *                                        should be written.
     * @throws \Exception if unknown serialization was given.
     */
    public function serializeIteratorToStream(StatementIterator $statements, $outputStream)
    {
        /*
         * check parameter $outputStream
         */
        if (is_resource($outputStream)) {
            // use it as it is

        } elseif (is_string($outputStream)) {
            $outputStream = fopen($outputStream, 'w');

        } else {
            throw new \Exception('Parameter $outputStream is neither a string nor resource.');
        }

        /*
         * Handle format
         */
        if ('n-quads' == $this->serialization) {
            $function = 'toNQuads';
        } elseif ('n-triples' == $this->serialization) {
            $function = 'toNTriples';
        }
        // no else need here, because there is a check in the constructor

        foreach ($statements as $statement) {
            fwrite($outputStream, $statement->$function() . PHP_EOL);
        }

        // Do not close the stream, because ... it is a stream and not a file! Well, maybe the user uses a
        // file, but its handler will be closed by PHP automatically at the end anyway.
    }

    /**
     * Returns a list of all supported serialization types.
     *
     * @return array Array of supported serialization types which can be used by this serializer.
     */
    public function getSupportedSerializations()
    {
        return array('n-triples', 'n-quads');
    }
}
