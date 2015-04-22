<?php

namespace Saft\Store;

use Saft\Rdf;
use Saft\Rdf\Node;
use Saft\Rdf\Statement;
use Saft\Rdf\StatementIterator;
use Saft\Sparql\ResultIterator;
use Saft\Sparql\Result\Result;

/**
 * Declaration of methods that any Store implementation must have, whether its
 * for a Triple- or Quad store.
 */
interface Store
{
    /**
     * Returns a list of all available graph URIs of the store.
     * It can also respect access control, to only returned available graphs in the current context.
     *
     * @return array Simple array of graph URIs.
     */
    public function getAvailableGraphs();

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
    public function addStatements(StatementIterator $Statements, Node $graph = null, array $options = array());

    /**
     * Removes all statements from a (default-) graph which match with given statement.
     *
     * @param  Statement $Statement          It can be either a concrete or pattern-statement.
     * @param  Node      $graph     optional Overrides target graph. If set, all statements will be delete in
     *                                       that graph.
     * @param  array     $options   optional It contains key-value pairs and should provide additional
     *                                       introductions for the store and/or its adapter(s).
     * @return boolean Returns true, if function performed without errors. In case
     *                 an error occur, an exception will be thrown.
     */
    public function deleteMatchingStatements(Statement $Statement, Node $graph = null, array $options = array());

    /**
     * It gets all statements of a given graph which match the following conditions:
     * - statement's subject is either equal to the subject of the same statement of the graph or it is null.
     * - statement's predicate is either equal to the predicate of the same statement of the graph or it is null.
     * - statement's object is either equal to the object of a statement of the graph or it is null.
     *
     * @param  Statement $Statement          It can be either a concrete or pattern-statement.
     * @param  Node      $graph     optional Overrides target graph. If set, you will get all
     *                                       matching statements of that graph.
     * @param  array     $options   optional It contains key-value pairs and should provide additional
     *                                       introductions for the store and/or its adapter(s).
     * @return StatementIterator It contains Statement instances  of all matching statements of the given graph.
     */
    public function getMatchingStatements(Statement $Statement, Node $graph = null, array $options = array());

    /**
     * Returns true or false depending on whether or not the statements pattern
     * has any matches in the given graph.
     *
     * @param  Statement $Statement          It can be either a concrete or pattern-statement.
     * @param  Node      $graph     optional Overrides target graph.
     * @param  array     $options   optional It contains key-value pairs and should provide additional
     *                                       introductions for the store and/or its adapter(s).
     * @return boolean Returns true if at least one match was found, false otherwise.
     */
    public function hasMatchingStatement(Statement $Statement, Node $graph = null, array $options = array());

    /**
     * Get information about the store and its features.
     *
     * @return array Array which contains information about the store and its features.
     */
    public function getStoreDescription();

    /**
     * This method sends a SPARQL query to the store.
     *
     * @param  string $query            The SPARQL query to send to the store.
     * @param  array  $options optional It contains key-value pairs and should provide additional
     *                                  introductions for the store and/or its adapter(s).
     * @return Result Returns result of the query. Depending on the query type, it returns either an instance
     *                of EmptyResult, ExceptionResult, SetResult, StatementResult or ValueResult.
     * @throws \Exception If query is no string.
     * @throws \Exception If query is malformed.
     */
    public function query($query, array $options = array());
}
