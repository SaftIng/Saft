<?php

namespace Saft\Skeleton\Data;

use Saft\Addition\EasyRdf\Data\ParserFactoryEasyRdf;
use Saft\Rdf\NodeFactory;
use Saft\Rdf\StatementFactory;

/**
 * This factory creates the most suitable parser instance for a given serialization.
 */
class ParserFactory
{
    /**
     * @var NodeFactory
     */
    protected $nodeFactory;

    /**
     * @var StatementFactory
     */
    protected $statementFactory;

    /**
     * @param NodeFactory $nodeFactory
     * @param StatementFactory $statementFactory
     */
    public function __construct(NodeFactory $nodeFactory, StatementFactory $statementFactory)
    {
        $this->nodeFactory = $nodeFactory;
        $this->statementFactory = $statementFactory;
    }

    /**
     * @param string $serialization
     */
    public function createParserFor($serialization)
    {
        $easyRdfParserFactory = new ParserFactoryEasyRdf($this->nodeFactory, $this->statementFactory);

        // if EasyRdf supports the given serialization
        if (in_array($serialization, $easyRdfParserFactory->getSupportedSerializations())) {
            return $easyRdfParserFactory->createParserFor($serialization);
        }

        return null;
    }
}
