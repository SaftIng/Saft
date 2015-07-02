<?php

namespace Saft\Addition\EasyRdf\Data;

use EasyRdf\Format;
use EasyRdf\Graph;
use Saft\Data\Parser;
use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\NodeFactory;
use Saft\Rdf\NodeUtils;
use Saft\Rdf\StatementFactory;

class ParserEasyRdf implements Parser
{
    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @var StatementFactory
     */
    private $statementFactory;

    /**
     * Constructor.
     *
     * @param NodeFactory      $nodeFactory
     * @param StatementFactory $statementFactory
     * @param \EasyRdf\Format  $serialization
     */
    public function __construct(NodeFactory $nodeFactory, StatementFactory $statementFactory, Format $serialization)
    {
        $this->nodeFactory = $nodeFactory;
        $this->statementFactory = $statementFactory;
        $this->serialization = $serialization;
    }

    /**
     * Returns an array of prefixes which where found during the last parsing.
     *
     * @return array An associative array with a prefix mapping of the prefixes parsed so far. The key
     *               will be the prefix, while the values contains the according namespace URI.
     */
    public function getCurrentPrefixList()
    {
        // TODO implement a way to get a list of all namespaces used in the last parsed datastring/file.
        return array();
    }

    /**
     * Parses a given string and returns an iterator containing Statement instances representing the
     * previously read data.
     *
     * @param  string            $inputString   Data string containing RDF serialized data.
     * @param  string            $baseUri       The base URI of the parsed content. If this URI is null the
     *                                          inputStreams URL is taken as base URI.
     * @param  string            $serialization The serialization of the inputStream. If null is given the
     *                                          parser will either apply some standard serialization, or the
     *                                          only one it is supporting, or will try to guess the correct
     *                                          serialization, or will throw an Exception.
     *                                          For more information have a look here:
     *                                          http://safting.github.io/doc/phpframework/data/
     * @return StatementIterator StatementIterator instaince containing all the Statements parsed by the
     *                           parser to far
     * @throws \Exception        If the base URI $baseUri is no valid URI.
     */
    public function parseStringToIterator($inputString, $baseUri = null)
    {
        $graph = new Graph();

        $graph->parse($inputString, $this->serialization->getName(), $baseUri);

        // transform parsed data to PHP array
        return $this->rdfPhpToStatementIterator($graph->toRdfPhp());
    }

    /**
     * Parses a given stream and returns an iterator containing Statement instances representing the
     * previously read data. The stream parses the data not as a whole but in chunks.
     *
     * @param  string            $inputStream   Filename of the stream to parse which contains RDF
     *                                          serialized data.
     * @param  string            $baseUri       The base URI of the parsed content. If this URI is null
     *                                          the inputStreams URL is taken as base URI.
     * @param  string            $serialization The serialization of the inputStream. If null is given
     *                                          the parser will either apply some standard serialization,
     *                                          or the only one it is supporting, or will try to guess
     *                                          the correct serialization, or will throw an Exception.
     *                                          Supported formats are a subset of the following:
     *                                          json, rdfxml, sparql-xml, rdfa, turtle, ntriples, n3
     * @return StatementIterator A StatementIterator containing all the Statements parsed by the parser to
     *                           far.
     * @throws \Exception        If the base URI $baseUri is no valid URI.
     */
    public function parseStreamToIterator($inputStream, $baseUri = null)
    {
        $graph = new Graph();

        $graph->parseFile($inputStream, $this->serialization->getName());

        // transform parsed data to PHP array
        return $this->rdfPhpToStatementIterator($graph->toRdfPhp());
    }

    /**
     * Transforms a statement array given by EasyRdf to a Saft StatementIterator instance.
     *
     * @param  array             $rdfPhp
     * @return StatementIterator
     */
    protected function rdfPhpToStatementIterator(array $rdfPhp)
    {
        $statements = array();

        // go through all subjects
        foreach ($rdfPhp as $subject => $predicates) {
            // predicates associated with the subject
            foreach ($predicates as $property => $objects) {
                // object(s)
                foreach ($objects as $object) {
                    /**
                     * Create subject node
                     */
                    if (true === NodeUtils::simpleCheckURI($subject)) {
                        $s = $this->nodeFactory->createNamedNode($subject);
                    } else {
                        $s = $this->nodeFactory->createLiteral($subject);
                    }

                    /**
                     * Create predicate node
                     */
                    if (true === NodeUtils::simpleCheckURI($property)) {
                        $p = $this->nodeFactory->createNamedNode($property);
                    } else {
                        $p = $this->nodeFactory->createLiteral($property);
                    }

                    /*
                     * Create object node
                     */
                    // URI
                    if (NodeUtils::simpleCheckURI($object['value'])) {
                        $o = $this->nodeFactory->createNamedNode($object['value']);

                    // datatype set
                    } elseif (isset($object['datatype'])) {
                        $o = $this->nodeFactory->createLiteral($object['value'], $object['datatype']);

                    // lang set
                    } elseif (isset($object['lang'])) {
                        $o = $this->nodeFactory->createLiteral(
                            $object['value'],
                            'http://www.w3.org/1999/02/22-rdf-syntax-ns#langString',
                            $object['lang']
                        );
                    } else {
                        $o = $this->nodeFactory->createLiteral($object['value']);
                    }

                    // build and add statement
                    $statements[] = $this->statementFactory->createStatement($s, $p, $o);
                }
            }
        }

        return new ArrayStatementIteratorImpl($statements);
    }
}
