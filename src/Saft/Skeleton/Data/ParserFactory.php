<?php

namespace Saft\Skeleton\Data;

use Saft\Addition\EasyRdf\Data\ParserFactoryEasyRdf;
use Saft\Data\RDFXMLParser;
use Saft\Data\ParserFactory as ParserFactoryInterface;
use Saft\Rdf\NodeFactory;
use Saft\Rdf\NodeUtils;
use Saft\Rdf\StatementFactory;

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
     * @param NodeFactory $nodeFactory
     * @param StatementFactory $statementFactory
     * @param NodeUtils $nodeUtils
     */
    public function __construct(NodeFactory $nodeFactory, StatementFactory $statementFactory, NodeUtils $nodeUtils)
    {
        $this->nodeFactory = $nodeFactory;
        $this->statementFactory = $statementFactory;
        $this->nodeUtils = $nodeUtils;
    }

    /**
     * @param string $serialization
     * @return null|Parser
     */
    public function createParserFor($serialization)
    {
        if ('rdf-xml' == $serialization) {
            return new RDFXMLParser($this->nodeFactory, $this->statementFactory, $this->nodeUtils);
        }

        $easyRdfParserFactory = new ParserFactoryEasyRdf($this->nodeFactory, $this->statementFactory);

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
        $easyRdfParserFactory = new ParserFactoryEasyRdf($this->nodeFactory, $this->statementFactory);
        return $easyRdfParserFactory->getSupportedSerializations();
    }
}
