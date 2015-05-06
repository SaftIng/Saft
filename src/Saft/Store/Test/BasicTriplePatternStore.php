<?php

namespace Saft\Store\Test;

use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\Node;
use Saft\Rdf\NodeFactory;
use Saft\Rdf\Statement;
use Saft\Rdf\StatementIterator;
use Saft\Store\AbstractTriplePatternStore;
use Saft\Store\Store;

/**
 * This is a basic resp. simple implementation of the Store interface using the AbstractTriplePatternStore. Its purpose
 * is to serve as mock store in test cases.
 */
class BasicTriplePatternStore extends AbstractTriplePatternStore
{
    public function __construct(NodeFactory $nodeFactory)
    {
        parent::__construct($nodeFactory);
    }

    /**
     * Has no function and returns an empty array.
     *
     * @return array Empty array
     */
    public function getAvailableGraphs()
    {
        return array();
    }

    /**
     * Has no function and returns an empty array.
     *
     * @return array Empty array
     */
    public function getStoreDescription()
    {
        return array();
    }

    /**
     * Adds multiple Statements to (default-) graph.
     *
     * @param  StatementIterator $statements          StatementList instance must contain Statement instances
     *                                                which are 'concret-' and not 'pattern'-statements.
     * @param  Node              $graph      optional Overrides target graph. If set, all statements will
     *                                                be add to that graph, if available.
     * @param  array             $options    optional It contains key-value pairs and should provide additional
     *                                                introductions for the store and/or its adapter(s).
     * @return boolean Returns true, if function performed without errors. In case an error occur, an exception
     *                 will be thrown.
     */
    public function addStatements(StatementIterator $Statements, Node $graph = null, array $options = array())
    {
        return true;
    }

    /**
     * Removes all statements from a (default-) graph which match with given statement.
     *
     * @param  Statement $Statement It can be either a concrete or pattern-statement.
     * @param  Node $graph optional Overrides target graph. If set, all statements will be delete in
     *                                       that graph.
     * @param  array $options optional It contains key-value pairs and should provide additional
     *                                       introductions for the store and/or its adapter(s).
     * @return boolean Returns true, if function performed without errors. In case an error occur, an exception
     *                 will be thrown.
     */
    public function deleteMatchingStatements(Statement $Statement, Node $graph = null, array $options = array())
    {
        return true;
    }

    /**
     * It gets all statements of a given graph which match the following conditions:
     * - statement's subject is either equal to the subject of the same statement of the graph or it is null.
     * - statement's predicate is either equal to the predicate of the same statement of the graph or it is null.
     * - statement's object is either equal to the object of a statement of the graph or it is null.
     *
     * @param  Statement $Statement It can be either a concrete or pattern-statement.
     * @param  Node $graph optional Overrides target graph. If set, you will get all
     *                                       matching statements of that graph.
     * @param  array $options optional It contains key-value pairs and should provide additional
     *                                       introductions for the store and/or its adapter(s).
     * @return StatementIterator It contains Statement instances  of all matching statements of the given graph.
     */
    public function getMatchingStatements(Statement $Statement, Node $graph = null, array $options = array())
    {
        return new ArrayStatementIteratorImpl(array());
    }

    /**
     * Returns true or false depending on whether or not the statements pattern has any matches in the given graph.
     *
     * @param  Statement $Statement It can be either a concrete or pattern-statement.
     * @param  Node $graph optional Overrides target graph.
     * @param  array $options optional It contains key-value pairs and should provide additional
     *                                       introductions for the store and/or its adapter(s).
     * @return boolean Returns true if at least one match was found, false otherwise.
     */
    public function hasMatchingStatement(Statement $Statement, Node $graph = null, array $options = array())
    {
        return true;
    }

    /**
     * Create a new graph with the URI given as Node. If the underlying store implementation doesn't support empty
     * graphs this method will have no effect.
     *
     * @param Node $graph The graph name used for the newly created graph
     * @param array $options optional additional key-value pairs passed to the store implementation
     *
     * @throws \Exception If the given graph could not be created
     */
    public function createGraph(Node $graph, array $options = array())
    {
        // TODO: Implement createGraph() method.
    }

    /**
     * Removes the given graph from the store.
     *
     * @param Node $graph The name of the graph to drop
     * @param array $options optional additional key-value pairs passed to the store implementation
     *
     * @throws \Exception If the given graph could not be droped
     */
    public function dropGraph(Node $graph, array $options = array())
    {
        // TODO: Implement dropGraph() method.
    }
}
