<?php

namespace Saft\Addition\EasyRdf\Data;

use Saft\Data\ParserFactory;
use Saft\Rdf\NodeFactory;
use Saft\Rdf\NodeUtils;
use Saft\Rdf\StatementFactory;
use Saft\Rdf\StatementIteratorFactory;

class ParserFactoryEasyRdf implements ParserFactory
{
    /**
     * @var array
     */
    protected $serializationMap;

    protected $nodeFactory;

    protected $nodeUtils;

    protected $statementFactory;

    protected $statementIteratorFactory;

    public function __construct(
        NodeFactory $nodeFactory,
        StatementFactory $statementFactory,
        StatementIteratorFactory $statementIteratorFactory,
        NodeUtils $nodeUtils
    ) {
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

        $this->nodeFactory = $nodeFactory;
        $this->nodeUtils = $nodeUtils;
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

        return new ParserEasyRdf(
            $this->nodeFactory,
            $this->statementFactory,
            $this->statementIteratorFactory,
            $this->nodeUtils,
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
