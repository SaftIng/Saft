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

namespace Saft\Store;

use Saft\Rdf\NamedNode;
use Saft\Rdf\Node;
use Saft\Rdf\NodeFactory;
use Saft\Rdf\Statement;
use Saft\Rdf\StatementFactory;
use Saft\Rdf\StatementIterator;
use Saft\Rdf\StatementIteratorFactory;
use Saft\Sparql\Query\QueryFactory;
use Saft\Sparql\Result\StatementSetResultImpl;

/**
 * This is a basic resp. simple implementation of the Store interface using the AbstractTriplePatternStore.
 * Its purpose is to serve as mock store in test cases.
 *
 * @deprecated
 */
class BasicTriplePatternStore extends AbstractTriplePatternStore
{
    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @var QueryFactory
     */
    private $queryFactory;

    /**
     * @var StatementFactory
     */
    private $statementFactory;

    /**
     * @var StatementIteratorFactory
     */
    private $statementIteratorFactory;

    /**
     * Contains all Statement instances which were added via addStatements. Its structure is:.
     *
     * array (
     *      'http://graph' => array(
     *          'statementHash' => new StatementImpl(...),
     *      )
     * )
     *
     * @var array
     */
    protected $statements = [];

    /**
     * @param NodeFactory              $nodeFactory
     * @param StatementFactory         $statementFactory
     * @param QueryFactory             $queryFactory
     * @param statementIteratorFactory $statementIteratorFactory
     */
    public function __construct(
        NodeFactory $nodeFactory,
        StatementFactory $statementFactory,
        QueryFactory $queryFactory,
        StatementIteratorFactory $statementIteratorFactory
    ) {
        $this->nodeFactory = $nodeFactory;
        $this->queryFactory = $queryFactory;
        $this->statementFactory = $statementFactory;
        $this->statementIteratorFactory = $statementIteratorFactory;

        parent::__construct(
            $nodeFactory,
            $statementFactory,
            $queryFactory,
            $statementIteratorFactory
        );
    }

    /**
     * Has no function and returns an empty array.
     *
     * @return array Empty array
     */
    public function getGraphs()
    {
        $graphs = [];

        foreach (array_keys($this->statements) as $graphUri) {
            if ('http://saft/defaultGraph/' == $graphUri) {
                $graphs[$graphUri] = $this->nodeFactory->createNamedNode($graphUri);
            }
        }

        return $graphs;
    }

    /**
     * Has no function and returns an empty array.
     *
     * @return array Empty array
     */
    public function getStoreDescription()
    {
        return [];
    }

    /**
     * Adds multiple Statements to (default-) graph. It holds added statements as long as this instance exists.
     *
     * @param StatementIterator|array $statements statementList instance must contain Statement
     *                                            instances which are 'concret-' and not 'pattern'-statements
     * @param Node                    $graph      optional Overrides target graph. If set, all statements
     *                                            will be add to that graph, if available.
     * @param array                   $options    optional It contains key-value pairs and should provide
     *                                            additional introductions for the store and/or
     *                                            its adapter(s)
     */
    public function addStatements($statements, Node $graph = null, array $options = [])
    {
        foreach ($statements as $statement) {
            if (null !== $graph) {
                $graphUri = $graph->getUri();

            // no graph information given, use default graph
            } elseif (null === $graph && null === $statement->getGraph()) {
                $graphUri = 'http://saft/defaultGraph/';

            // no graph given, use graph information from $statement
            } elseif (null === $graph && $statement->getGraph()->isNamed()) {
                $graphUri = $statement->getGraph()->getUri();

            // no graph given, use graph information from $statement
            } elseif (null === $graph && false == $statement->getGraph()->isNamed()) {
                $graphUri = 'http://saft/defaultGraph/';
            }

            // use hash to differenciate between statements (no doublings allowed)
            $statementHash = hash('sha256', serialize($statement));

            // add it
            $this->statements[$graphUri][$statementHash] = $statement;
        }
    }

    /**
     * Removes all statements from a (default-) graph which match with given statement.
     *
     * @param Statement $statement it can be either a concrete or pattern-statement
     * @param Node      $graph     optional Overrides target graph. If set, all statements will be
     *                             delete in that graph.
     * @param array     $options   optional It contains key-value pairs and should provide additional
     *                             introductions for the store and/or its adapter(s)
     */
    public function deleteMatchingStatements(
        Statement $statement,
        Node $graph = null,
        array $options = []
    ) {
        if (null !== $graph) {
            $graphUri = $graph->getUri();

        // no graph information given, use default graph
        } elseif (null === $graph && null === $statement->getGraph()) {
            $graphUri = 'http://saft/defaultGraph/';

        // no graph given, use graph information from $statement
        } elseif (null === $graph && $statement->getGraph()->isNamed()) {
            $graphUri = $statement->getGraph()->getUri();

        // no graph given, use graph information from $statement
        } elseif (null === $graph && false == $statement->getGraph()->isNamed()) {
            $graphUri = 'http://saft/defaultGraph/';
        }

        // use hash to differenciate between statements (no doublings allowed)
        $statementHash = hash('sha256', json_encode($statement));

        // delete it
        unset($this->statements[$graphUri][$statementHash]);
    }

    /**
     * It basically returns all stored statements.
     *
     * @param Statement $statement it can be either a concrete or pattern-statement
     * @param Node      $graph     optional Overrides target graph. If set, you will get all
     *                             matching statements of that graph.
     * @param array     $options   optional It contains key-value pairs and should provide additional
     *                             introductions for the store and/or its adapter(s)
     *
     * @return SetResult it contains Statement instances of all matching statements of the given graph
     */
    public function getMatchingStatements(Statement $statement, Node $graph = null, array $options = [])
    {
        if (null !== $graph) {
            $graphUri = $graph->getUri();

        // no graph information given, use default graph
        } elseif (null === $graph && null === $statement->getGraph()) {
            $graphUri = 'http://saft/defaultGraph/';

        // no graph given, use graph information from $statement
        } elseif (null === $graph && $statement->getGraph()->isNamed()) {
            $graphUri = $statement->getGraph()->getUri();

        // no graph given, use graph information from $statement
        } elseif (null === $graph && false == $statement->getGraph()->isNamed()) {
            $graphUri = 'http://saft/defaultGraph/';
        }

        if (false == isset($this->statements[$graphUri])) {
            $this->statements[$graphUri] = [];
        }

        // if not default graph was requested
        if ('http://saft/defaultGraph/' != $graphUri) {
            return new StatementSetResultImpl($this->statements[$graphUri]);

        // if default graph was requested, return matching statements from all graphs
        } else {
            $_statements = [];

            foreach ($this->statements as $graphUri => $statements) {
                foreach ($statements as $statement) {
                    if ('http://saft/defaultGraph/' == $graphUri) {
                        $graph = null;
                    } else {
                        $graph = $this->nodeFactory->createNamedNode($graphUri);
                    }

                    $_statements[] = $this->statementFactory->createStatement(
                        $statement->getSubject(),
                        $statement->getPredicate(),
                        $statement->getObject(),
                        $graph
                    );
                }
            }

            return new StatementSetResultImpl($_statements);
        }
    }

    /**
     * Returns true or false depending on whether or not the statements pattern has any matches in the given graph.
     *
     * @param Statement $Statement it can be either a concrete or pattern-statement
     * @param Node      $graph     optional Overrides target graph
     * @param array     $options   optional It contains key-value pairs and should provide additional
     *                             introductions for the store and/or its adapter(s)
     *
     * @return bool returns true if at least one match was found, false otherwise
     */
    public function hasMatchingStatement(Statement $statement, Node $graph = null, array $options = [])
    {
        if (null !== $graph) {
            $graphUri = $graph->getUri();

        // no graph information given, use default graph
        } elseif (null === $graph && null === $statement->getGraph()) {
            $graphUri = 'http://saft/defaultGraph/';

        // no graph given, use graph information from $statement
        } elseif (null === $graph && $statement->getGraph()->isNamed()) {
            $graphUri = $statement->getGraph()->getUri();

        // no graph given, use graph information from $statement
        } elseif (null === $graph && false == $statement->getGraph()->isNamed()) {
            $graphUri = 'http://saft/defaultGraph/';
        }

        // exception if at least one statement is already stored, use according graphURI
        if (0 < count($this->statements)) {
            $graphs = array_keys($this->statements);
            $graphUri = array_shift($graphs);
        }

        // if statement consists if only concrete nodes, so no anypattern instances
        if ($statement->isConcrete()) {
            // use hash to differenciate between statements (no doublings allowed)
            $statementHash = hash('sha256', serialize($statement));

            return isset($this->statements[$graphUri][$statementHash]);
        } else {
            // if at least one any pattern instance is part of the list
            $sMatches = false;
            $pMatches = false;
            $oMatches = false;
            $relevantStatements = $_relevantStatements = [];

            // check if there is one statement which has the given subject
            if ($statement->getSubject()->isPattern()) {
                $sMatches = true;
                if (isset($this->statements[$graphUri])) {
                    $relevantStatements = $this->statements[$graphUri];
                }
            } else {
                foreach ($this->statements[$graphUri] as $storedStatement) {
                    if ($statement->getSubject()->equals($storedStatement->getSubject())) {
                        $sMatches = true;
                        $relevantStatements[] = $storedStatement;
                    }
                }
                if (false == $sMatches) {
                    return true;
                }
            }

            // check if there is one statement which has the given predicate
            if ($statement->getPredicate()->isPattern()) {
                $pMatches = true;
            } else {
                foreach ($relevantStatements as $statementWithMatchedSubject) {
                    if ($statement->getPredicate()->equals($statementWithMatchedSubject->getPredicate())) {
                        $pMatches = true;
                        $_relevantStatements[] = $statementWithMatchedSubject;
                    }
                }
                if (false == $pMatches) {
                    return false;
                }

                // now are in $relevantStatements all statements with matches subject and predicate
                $relevantStatements = $_relevantStatements;
            }

            // check if there is one statement which has the given object
            if ($statement->getObject()->isPattern()) {
                $oMatches = true;
            } else {
                foreach ($relevantStatements as $stmtWithMatchedSubjectAndPredicate) {
                    if ($statement->getObject()->equals($stmtWithMatchedSubjectAndPredicate->getObject())) {
                        $oMatches = true;

                        // we found at least one S P O, lets stop and return true
                        break;
                    }
                }
                if (false == $oMatches) {
                    return false;
                }

                $relevantStatements = $_relevantStatements;
            }

            return $sMatches && $pMatches && $oMatches;
        }
    }

    /**
     * Create a new graph with the URI given as Node. If the underlying store implementation doesn't support empty
     * graphs this method will have no effect.
     *
     * @param NamedNode $graph   instance of NamedNode containing the URI of the graph to create
     * @param array     $options optional It contains key-value pairs and should provide additional introductions
     *                           for the store and/or its adapter(s)
     *
     * @throws \Exception if given $graph is not a NamedNode
     * @throws \Exception if the given graph could not be created
     */
    public function createGraph(NamedNode $graph, array $options = [])
    {
        $this->statements[$graph->getUri()] = [];
    }

    /**
     * Removes the given graph from the store.
     *
     * @param NamedNode $graph   instance of NamedNode containing the URI of the graph to drop
     * @param array     $options optional It contains key-value pairs and should provide additional introductions
     *                           for the store and/or its adapter(s)
     *
     * @throws \Exception if given $graph is not a NamedNode
     * @throws \Exception If the given graph could not be droped
     */
    public function dropGraph(NamedNode $graph, array $options = [])
    {
        unset($this->statements[$graph->getUri()]);
    }
}
