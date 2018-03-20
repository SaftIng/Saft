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

namespace Saft\Addition\EasyRdf\Data;

use Saft\Data\Serializer;
use Saft\Rdf\StatementIterator;

class SerializerEasyRdf implements Serializer
{
    /**
     * @var array
     */
    protected $serializationMap;

    /**
     * Constructor.
     *
     * @param string $serialization serialization format, for instance turtle or rdfa
     *
     * @throws \Exception if unknown serialization format was given
     */
    public function __construct($serialization)
    {
        /*
         * Map of serializations. It maps the Saft term on according the EasyRdf format.
         */
        $this->serializationMap = [
            'n-triples' => 'ntriples',
            'rdf-json' => 'json',
            'rdf-xml' => 'rdfxml',
            'rdfa' => 'rdfa',
            'turtle' => 'turtle',
        ];

        if (false == isset($this->serializationMap[$serialization])) {
            throw new \Exception(
                'Unknown serialization format given: '.$serialization.'. Supported are only '.
                implode(', ', array_keys($this->serializationMap))
            );
        }

        // dont save the given serialization here, but the according one which EasyRdf understands.
        // there might be some small differences.
        $this->serialization = $this->serializationMap[$serialization];
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
     * @param StatementIterator $statements   the StatementIterator containing all the Statements which
     *                                        should be serialized by the serializer
     * @param string|resource   $outputStream filename or file pointer to the stream to where the serialization
     *                                        should be written
     *
     * @throws \Exception if unknown serilaization was given
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

        $graph = new \EasyRdf_Graph();

        // go through all statements
        foreach ($statements as $statement) {
            /*
             * Handle subject
             */
            $stmtSubject = $statement->getSubject();
            if ($stmtSubject->isNamed()) {
                $s = $stmtSubject->getUri();
            } elseif ($stmtSubject->isBlank()) {
                $s = '_:'.$stmtSubject->getBlankId();
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
                $o = ['type' => 'uri', 'value' => $stmtObject->getUri()];
            } elseif ($stmtObject->isBlank()) {
                $o = ['type' => 'bnode', 'value' => '_:'.$stmtObject->getBlankId()];
            } elseif ($stmtObject->isLiteral()) {
                $o = [
                    'type' => 'literal',
                    'value' => $stmtObject->getValue(),
                    'datatype' => $stmtObject->getDataType()->getUri(),
                ];
            } else {
                throw new \Exception('Object can either be a blank node, an URI or literal.');
            }

            $graph->add($s, $p, $o);
        }

        fwrite($outputStream, $graph->serialise($this->serialization).PHP_EOL);
    }

    /**
     * Returns a list of all supported serialization types.
     *
     * @return array array of supported serialization types which can be used by this serializer
     */
    public function getSupportedSerializations()
    {
        return array_keys($this->serializationMap);
    }
}
