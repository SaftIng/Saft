<?php

namespace Saft\Addition\EasyRdf\Data;

use EasyRdf\Format;
use EasyRdf\Graph;
use Saft\Data\Serializer;
use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\NodeFactory;
use Saft\Rdf\NodeUtils;
use Saft\Rdf\StatementFactory;
use Saft\Rdf\StatementIterator;
use Streamer\Stream;

class SerializerEasyRdf implements Serializer
{
    /**
     * @var array
     */
    protected $serializationMap;

    /**
     * Constructor.
     */
    public function __construct()
    {
        /**
         * Map of serializations. It maps the Saft term on according the EasyRdf format.
         */
        $this->serializationMap = array(
            'n-triples' => 'ntriples',
            'rdf-json' => 'json',
            'rdf-xml' => 'rdfxml',
            'rdfa' => 'rdfa',
            'turtle' => 'turtle',
        );
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
     * @param  StatementIterator $statements    The StatementIterator containing all the Statements which
     *                                          should be serialized by the serializer.
     * @param  string            $outputStream  filename of the stream to where the serialization should be
     *                                          written.
     * @param  string            $serialization The serialization which should be used. If null is given the
     *                                          serializer will either apply some default serialization, or
     *                                          the only one it is supporting, or will throw an Exception.
     * @throws \Exception If unknown serilaization was given.
     */
    public function serializeIteratorToStream(StatementIterator $statements, $outputStream, $serialization = null)
    {
        $stream = new Stream(fopen($outputStream, 'w'));

        // if no format was given, serialize to turtle.
        if (null == $serialization) {
            $format = 'turtle';
        }

        if (false === isset($this->serializationMap[$serialization])) {
            throw new \Exception ('Unknown serialization given: '. $serialization);
        }

        $graph = new Graph();

        // go through all statements
        foreach ($statements as $statement) {
            /*
             * Handle subject
             */
            $stmtSubject = $statement->getSubject();
            if ($stmtSubject->isNamed()) {
                $s = $stmtSubject->getUri();
            } elseif ($stmtSubject->isBlank()) {
                $s = $stmtSubject->getBlankId();
            } else {
                throw new \Exception('Subject can either be a blank node or an URI.');
            }

            /*
             * Handle predicate
             */
            $stmtPredicate = $statement->getPredicate();
            if ($stmtPredicate->isNamed()) {
                $p = $stmtPredicate->getUri();
            } else {
                throw new \Exception('Predicate can only be an URI.');
            }

            /*
             * Handle object
             */
            $stmtObject = $statement->getObject();
            if ($stmtObject->isNamed()) {
                $o = array('type' => 'uri', 'value' => $stmtObject->getUri());
            } elseif ($stmtObject->isBlank()) {
                $o = array('type' => 'bnode', 'value' => $stmtObject->getBlankId());
            } elseif ($stmtObject->isLiteral()) {
                $o = array('type' => 'literal', 'value' => $stmtObject->getValue());
            } else {
                throw new \Exception('Object can either be a blank node, an URI or literal.');
            }

            $graph->add($s, $p, $o);
        }

        $stream->write($graph->serialise($this->serializationMap[$serialization]) . PHP_EOL);

        $stream->close();
    }

    /**
     * Returns a list of all supported serialization types.
     *
     * @return array Array of supported serialization types which can be used by this serializer.
     */
    public function getSupportedSerializations()
    {
        return array_keys($this->serializationMap);
    }
}
