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

namespace Saft\Addition\hardf\Data;

use pietercolpaert\hardf\TriGParser;
use pietercolpaert\hardf\Util;
use Saft\Data\Parser;
use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\NodeFactory;
use Saft\Rdf\RdfHelpers;
use Saft\Rdf\StatementFactory;
use Saft\Rdf\StatementIteratorFactory;

class ParserHardf implements Parser
{
    /**
     * @var NodeFactory
     */
    protected $nodeFactory;

    /**
     * @var RdfHelpers
     */
    protected $rdfHelpers;

    /**
     * @var string
     */
    protected $serialization;

    /**
     * @var array
     */
    protected $serializationMap;

    /**
     * @var StatementFactory
     */
    protected $statementFactory;

    /**
     * Constructor.
     *
     * @param NodeFactory $nodeFactory
     * @param StatementFactory $statementFactory
     * @param StatementIteratorFactory $statementIteratorFactory
     * @param RdfHelpers $rdfHelpers
     * @param string $serialization
     * @throws \Exception if serialization is unknown.
     */
    public function __construct(
        NodeFactory $nodeFactory,
        StatementFactory $statementFactory,
        StatementIteratorFactory $statementIteratorFactory,
        RdfHelpers $rdfHelpers,
        $serialization
    ) {
        $this->RdfHelpers = $rdfHelpers;

        $this->nodeFactory = $nodeFactory;
        $this->statementFactory = $statementFactory;
        $this->statementIteratorFactory = $statementIteratorFactory;

        $this->serializationMap = array(
            'n-triples' => 'triple',
            'n-quads' => 'quad',
            'turtle' => 'turtle',
        );

        $this->serialization = $this->serializationMap[$serialization];

        if (false == isset($this->serializationMap[$serialization])) {
            throw new \Exception(
                'Unknown serialization format given: '. $serialization .'. Supported are only '.
                implode(', ', array_keys($this->serializationMap))
            );
        }
    }

    /**
     * Returns an array of prefixes which where found during the last parsing.
     *
     * @return array An associative array with a prefix mapping of the prefixes parsed so far. The key
     *               will be the prefix, while the values contains the according namespace URI.
     * @throws \Exception currently not implemented.
     */
    public function getCurrentPrefixList()
    {
        throw new \Exception('Currently not implemented.');
    }

    /**
     * Parses a given string and returns an iterator containing Statement instances representing the read data.
     *
     * @param  string $inputString Data string containing RDF serialized data.
     * @param  string $baseUri     The base URI of the parsed content. If this URI is null the inputStreams URL
     *                             is taken as base URI.
     * @return StatementIterator StatementIterator instaince containing all the Statements parsed by the
     *                           parser to far
     * @throws \Exception if the base URI $baseUri is no valid URI.
     */
    public function parseStringToIterator($inputString, $baseUri = null)
    {
        // check $baseUri
        if (null !== $baseUri && false == $this->RdfHelpers->simpleCheckURI($baseUri)) {
            throw new \Exception('Parameter $baseUri is not a valid URI.');
        }

        $statements = array();

        $parser = new TriGParser(array('format' => $this->serialization));
        $triples = $parser->parse($inputString);

        foreach ($triples as $triple) {
            /*
             * handle subject
             */
            $subject = null;
            if (Util::isIRI($triple['subject'])) {
                $subject = $this->nodeFactory->createNamedNode($triple['subject']);
            } elseif (Util::isBlank($triple['subject'])) {
                $subject = $this->nodeFactory->createBlankNode(substr($triple['subject'], 2));
            } else {
                throw new \Exception('Invalid node type for subject found: '. $triple['subject']);
            }

            /*
             * handle predicate
             */
            $predicate = null;
            if (Util::isIRI($triple['predicate'])) {
                $predicate = $this->nodeFactory->createNamedNode($triple['predicate']);
            } else {
                throw new \Exception('Invalid node type for predicate found: '. $triple['predicate']);
            }

            /*
             * handle object
             */
            $object = null;
            if (Util::isIRI($triple['object'])) {
                $object = $this->nodeFactory->createNamedNode($triple['object']);

            } elseif (Util::isBlank($triple['object'])) {
                $object = $this->nodeFactory->createBlankNode(substr($triple['object'], 2));

            } elseif (Util::isLiteral($triple['object'])) {
                // safety check, to avoid fatal error about missing Error class in hardf
                // FYI: https://github.com/pietercolpaert/hardf/pull/12
                // TODO: remove this here, if fixed
                $int = preg_match('/"(\n+\s*.*\n+\s*)"/si', $triple['object'], $match);
                if (0 < $int) {
                    $value = $match[1];
                    $lang = null;
                    $datatype = null;

                /*
                 * normal case
                 */
                } else {
                    // get value
                    preg_match('/"(.*)"/si', $triple['object'], $match);
                    $value = $match[1];

                    $lang = Util::getLiteralLanguage($triple['object']);
                    $lang = '' == $lang ? null : $lang;
                    $datatype = Util::getLiteralType($triple['object']);
                }

                $object = $this->nodeFactory->createLiteral($value, $datatype, $lang);
            } else {
                throw new \Exception('Invalid node type for object found: '. $triple['object']);
            }

            // add statement
            $statements[] = $this->statementFactory->createStatement($subject, $predicate, $object);
        }

        return $this->statementIteratorFactory->createStatementIteratorFromArray($statements);
    }

    /**
     * Parses a given stream and returns an iterator containing Statement instances representing the
     * previously read data. The stream parses the data not as a whole but in chunks.
     *
     * @param  string $inputStream Filename of the stream to parse which contains RDF serialized data.
     * @param  string $baseUri     The base URI of the parsed content. If this URI is null the inputStreams URL
     *                             is taken as base URI.
     * @return StatementIterator A StatementIterator containing all the Statements parsed by the parser to far.
     * @throws \Exception if the base URI $baseUri is no valid URI.
     */
    public function parseStreamToIterator($inputStream, $baseUri = null)
    {
        // check $baseUri
        if (null !== $baseUri && false == $this->RdfHelpers->simpleCheckURI($baseUri)) {
            throw new \Exception('Parameter $baseUri is not a valid URI.');
        }

        return $this->parseStringToIterator(file_get_contents($inputStream), $baseUri);
    }
}
