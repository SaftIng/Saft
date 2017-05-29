<?php

namespace Saft\Addition\hardf\Data;

use Saft\Data\ParserFactory;
use Saft\Rdf\NodeFactory;
use Saft\Rdf\RdfHelpers;
use Saft\Rdf\StatementFactory;
use Saft\Rdf\StatementIteratorFactory;

class ParserFactoryHardf implements ParserFactory
{
    /**
     * @var array
     */
    protected $serializationMap;

    protected $nodeFactory;

    protected $rdfHelpers;

    protected $statementFactory;

    protected $statementIteratorFactory;

    public function __construct(
        NodeFactory $nodeFactory,
        StatementFactory $statementFactory,
        StatementIteratorFactory $statementIteratorFactory,
        RdfHelpers $rdfHelpers
    ) {
        /**
         * Map of serializations. It maps the Saft term on according the EasyRdf format.
         */
        $this->serializationMap = array(
            'n-triples' => 'n-triples',
            'n-quads' => 'n-quads',
            'turtle' => 'turtle',
        );

        $this->nodeFactory = $nodeFactory;
        $this->RdfHelpers = $rdfHelpers;
        $this->statementFactory = $statementFactory;
        $this->statementIteratorFactory = $statementIteratorFactory;
    }

    /**
     * Creates a Parser instance for a given serialization, if available.
     *
     * @param string $serialization The serialization you need a parser for. In case it is not
     *                              available, an exception will be thrown.
     * @return Parser Suitable parser for the requested serialization.
     * @throws \Exception If parser for requested serialization is not available.
     */
    public function createParserFor($serialization)
    {
        if (!in_array($serialization, $this->getSupportedSerializations())) {
            throw new \Exception(
                'Requested serialization '. $serialization .' is not available in: '.
                implode(', ', $this->getSupportedSerializations())
            );
        }

        return new ParserHardf(
            $this->nodeFactory,
            $this->statementFactory,
            $this->statementIteratorFactory,
            $this->RdfHelpers,
            $serialization
        );
    }

    /**
    * Returns an array which contains supported serializations.
    *
    * @return array Array of supported serializations which are understood by this parser.
    */
    public function getSupportedSerializations()
    {
        return array_keys($this->serializationMap);
    }
}
