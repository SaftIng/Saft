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
use Saft\Addition\hardf\Rdf\LazyStatementIterator;
use Saft\Data\Parser;
use Saft\Rdf\NodeFactory;
use Saft\Rdf\RdfHelpers;
use Saft\Rdf\StatementFactory;
use Saft\Rdf\StatementIterator;
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
     * @param NodeFactory              $nodeFactory
     * @param StatementFactory         $statementFactory
     * @param StatementIteratorFactory $statementIteratorFactory
     * @param RdfHelpers               $rdfHelpers
     * @param string                   $serialization
     *
     * @throws \Exception if serialization is unknown
     */
    public function __construct(
        NodeFactory $nodeFactory,
        StatementFactory $statementFactory,
        StatementIteratorFactory $statementIteratorFactory,
        RdfHelpers $rdfHelpers,
        string $serialization
    ) {
        $this->RdfHelpers = $rdfHelpers;

        $this->nodeFactory = $nodeFactory;
        $this->statementFactory = $statementFactory;
        $this->statementIteratorFactory = $statementIteratorFactory;

        // for more information look near:
        // https://github.com/pietercolpaert/hardf/blob/master/src/TriGParser.php#L50
        $this->serializationMap = [
            'n-triples' => 'triple',
            'n-quads' => 'quad',
            'n3' => 'n3',
            'turtle' => 'turtle',
            'trig' => 'trig',
        ];

        if (false == isset($this->serializationMap[$serialization])) {
            throw new \Exception(
                'Unknown serialization format given: '.$serialization.'. Supported are only '.
                \implode(', ', \array_keys($this->serializationMap))
            );
        } else {
            $this->serialization = $this->serializationMap[$serialization];
        }
    }

    /**
     * Returns an array of prefixes which where found during the last parsing.
     *
     * @return array An associative array with a prefix mapping of the prefixes parsed so far. The key
     *               will be the prefix, while the values contains the according namespace URI.
     *
     * @throws \Exception currently not implemented
     */
    public function getCurrentPrefixList(): array
    {
        throw new \Exception('Currently not implemented.');
    }

    /**
     * Parses a given string and returns an iterator containing Statement instances representing the read data.
     *
     * @param string $inputString data string containing RDF serialized data
     * @param string $baseUri     The base URI of the parsed content. If this URI is null the inputStreams URL
     *                            is taken as base URI.
     *
     * @return StatementIterator StatementIterator instaince containing all the Statements parsed by the
     *                           parser to far
     *
     * @throws \Exception if the base URI $baseUri is no valid URI
     */
    public function parseStringToIterator(string $inputString, string $baseUri = null): StatementIterator
    {
        // check $baseUri
        if (null !== $baseUri && false == $this->RdfHelpers->simpleCheckURI($baseUri)) {
            throw new \Exception('Parameter $baseUri is not a valid URI.');
        }

        $statements = [];

        $parser = new TriGParser(['format' => $this->serialization]);
        $triples = $parser->parse($inputString);

        foreach ($triples as $triple) {
            // add statement
            $statements[] = saftAdditionHardfTripleToStatement($triple, $this->nodeFactory, $this->statementFactory);
        }

        return $this->statementIteratorFactory->createStatementIteratorFromArray($statements);
    }

    /**
     * Parses a given stream and returns an iterator previously read data. Stream is parsed in
     * chunks and returned StatementIterator instance operates on chunks.
     *
     * @param string $inputStream filename of the stream to parse which contains RDF serialized data
     * @param string $baseUri     The base URI of the parsed content. If this URI is null the inputStreams URL
     *                            is taken as base URI.
     *
     * @return StatementIterator LazyStatementIterator instance
     *
     * @throws \Exception if the base URI $baseUri is no valid URI
     */
    public function parseStreamToIterator(string $inputStream, string $baseUri = null): StatementIterator
    {
        // because n-triples is line based we can work with chunks very easily
        if (\in_array($this->serialization, ['triple', 'quad'])) {
            return new LazyStatementIterator(
                $inputStream,
                $this->serialization,
                $this->nodeFactory,
                $this->statementFactory,
                $baseUri
            );

        // for quads and turtle/n3
        } else {
            return $this->parseStringToIterator(\file_get_contents($inputStream), $baseUri);
        }
    }
}
