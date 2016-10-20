<?php

namespace Saft\Skeleton\Data;

use Saft\Addition\EasyRdf\Data\ParserFactoryEasyRdf;
use Saft\Data\NQuadsParser;
use Saft\Data\RDFXMLParser;
use Saft\Data\ParserFactory as ParserFactoryInterface;
use Saft\Rdf\NodeFactory;
use Saft\Rdf\NodeUtils;
use Saft\Rdf\StatementFactory;
use Saft\Rdf\StatementIteratorFactory;

/**
 * This factory creates the most suitable parser instance for a given serialization.
 */
class ParserFactory implements ParserFactoryInterface
{
    /**
     * @var NodeFactory
     */
    protected $nodeFactory;

    /**
     * @var NodeUtils
     */
    protected $nodeUtils;

    /**
     * @var StatementFactory
     */
    protected $statementFactory;

    /**
     * @var StatementIteratorFactory
     */
    protected $statementIteratorFactory;

    /**
     * @param NodeFactory $nodeFactory
     * @param StatementFactory $statementFactory
     * @param NodeUtils $nodeUtils
     */
    public function __construct(
        NodeFactory $nodeFactory,
        StatementFactory $statementFactory,
        StatementIteratorFactory $statementIteratorFactory,
        NodeUtils $nodeUtils
    ) {
        $this->nodeFactory = $nodeFactory;
        $this->nodeUtils = $nodeUtils;
        $this->statementFactory = $statementFactory;
        $this->statementIteratorFactory = $statementIteratorFactory;
    }

    /**
     * @param string $serialization
     * @return null|Parser
     */
    public function createParserFor($serialization)
    {
        // try first our own parsers
        if ('rdf-xml' == $serialization) {
            return new RDFXMLParser(
                $this->nodeFactory,
                $this->statementFactory,
                $this->statementIteratorFactory,
                $this->nodeUtils
            );
        } elseif ('n-triples' == $serialization || 'n-quads' == $serialization) {
            return new NQuadsParser(
                $this->nodeFactory,
                $this->statementFactory,
                $this->statementIteratorFactory,
                $this->nodeUtils
            );
        }

        $easyRdfParserFactory = new ParserFactoryEasyRdf(
            $this->nodeFactory, $this->statementFactory, $this->statementIteratorFactory, $this->nodeUtils
        );

        // if EasyRdf supports the given serialization
        if (in_array($serialization, $easyRdfParserFactory->getSupportedSerializations())) {
            return $easyRdfParserFactory->createParserFor($serialization);
        }

        return null;
    }

    /**
     * Returns supported serializations of all used parsers.
     *
     * @return array
     */
    public function getSupportedSerializations()
    {
        $easyRdfParserFactory = new ParserFactoryEasyRdf(
            $this->nodeFactory,
            $this->statementFactory,
            $this->statementIteratorFactory,
            $this->nodeUtils
        );

        return array_merge(
            array('n-triples', 'n-quads', 'rdf-xml'),
            $easyRdfParserFactory->getSupportedSerializations()
        );
    }
}
