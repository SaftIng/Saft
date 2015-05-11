<?php

namespace Saft\Backend\EasyRdf\Data;

use Saft\Data\Parser;
use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\NodeFactory;
use Saft\Rdf\NodeUtils;
use Saft\Rdf\StatementFactory;

abstract class AbstractParser implements Parser
{
    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @var StatementFactory
     */
    private $statementFactory;

    public function __construct(NodeFactory $nodeFactory, StatementFactory $statementFactory)
    {
        $this->nodeFactory = $nodeFactory;
        $this->statementFactory = $statementFactory;
    }

    /**
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

                    /**
                     * Create object node
                     */
                    if (true === NodeUtils::simpleCheckURI($object['value'])) {
                        $o = $this->nodeFactory->createNamedNode($object['value']);
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
