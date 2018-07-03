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
use Saft\Rdf\Statement;
use Saft\Rdf\StatementIterator;
use Saft\Sparql\Result\Result;

/**
 * Declaration of methods that any Store implementation must have, whether its for a Triple- or Quad store.
 *
 * @api
 *
 * @since 2.0.0
 */
interface Store
{
    /**
     * Adds multiple Statements to (default-) graph.
     *
     * @param StatementIterator|array $statements statementList instance must contain Statement instances which are
     *                                            'concret-' and not 'pattern'-statements
     * @param Node                    $graph      Overrides target graph. If set, all statements will be add to that
     *                                            graph, if it is available. (optional)
     * @param array                   $options    Key-value pairs which provide additional introductions for the store
     *                                            and/or its adapter(s). (optional)
     *
     * @api
     *
     * @since 1.0
     */
    public function addStatements(iterable $statements, Node $graph = null, array $options = []);

    /**
     * Removes all statements from a (default-) graph which match with given statement.
     *
     * @param Statement $statement it can be either a concrete or pattern-statement
     * @param Node      $graph     Overrides target graph. If set, all statements will be delete in that
     *                             graph. (optional)
     * @param array     $options   Key-value pairs which provide additional introductions for the store and/or its
     *                             adapter(s). (optional)
     *
     * @api
     *
     * @since 0.1
     */
    public function deleteMatchingStatements(Statement $statement, Node $graph = null, array $options = []);

    /**
     * It gets all statements of a given graph which match the following conditions:
     * - statement's subject is either equal to the subject of the same statement of the graph or
     *   it is null.
     * - statement's predicate is either equal to the predicate of the same statement of the graph or
     *   it is null.
     * - statement's object is either equal to the object of a statement of the graph or it is null.
     *
     * @param Statement $statement it can be either a concrete or pattern-statement
     * @param Node      $graph     Overrides target graph. If set, you will get all matching statements of
     *                             that graph. (optional)
     * @param array     $options   It contains key-value pairs and should provide additional introductions for
     *                             the store and/or its adapter(s). (optional)
     *
     * @return StatementIterator it contains Statement instances  of all matching statements of the given graph
     *
     * @api
     *
     * @since 0.1
     */
    public function getMatchingStatements(Statement $statement, Node $graph = null, array $options = []): StatementIterator;

    /**
     * Returns true or false depending on whether or not the statements pattern
     * has any matches in the given graph.
     *
     * @param Statement $statement it can be either a concrete or pattern-statement
     * @param Node      $graph     Overrides target graph. (optional)
     * @param array     $options   It contains key-value pairs and should provide additional introductions for the
     *                             store and/or its adapter(s). (optional)
     *
     * @return bool returns true if at least one match was found, false otherwise
     *
     * @api
     *
     * @since 0.1
     */
    public function hasMatchingStatement(Statement $statement, Node $graph = null, array $options = []): bool;

    /**
     * This method sends a SPARQL query to the store.
     *
     * @param string $query   the SPARQL query to send to the store
     * @param array  $options It contains key-value pairs and should provide additional introductions for the
     *                        store and/or its adapter(s). (optional)
     *
     * @return Result Returns result of the query. Its type depends on the type of the query.
     *
     * @throws \Exception if query is no string, is malformed or an execution error occured
     *
     * @api
     *
     * @since 0.1
     */
    public function query(string $query, array $options = []): Result;

    /**
     * Returns a list of all available graph URIs of the store. It can also respect access control,
     * to only returned available graphs in the current context. But that depends on the implementation
     * and can differ.
     *
     * @return array array with the graph URI as key and a NamedNode as value for each graph
     *
     * @api
     *
     * @since 2.0.0
     */
    public function getGraphs(): iterable;

    /**
     * Create a new graph with the URI given as Node. If the underlying store implementation doesn't
     * support empty graphs this method will have no effect.
     *
     * @param NamedNode $graph   instance of NamedNode containing the URI of the graph to create
     * @param array     $options it contains key-value pairs and should provide additional introductions for
     *                           the store and/or its adapter(s)
     *
     * @throws \Exception if given $graph is not a NamedNode
     * @throws \Exception if the given graph could not be created
     *
     * @api
     *
     * @since 0.1
     */
    public function createGraph(NamedNode $graph, array $options = []);

    /**
     * Removes the given graph from the store.
     *
     * @param NamedNode $graph   instance of NamedNode containing the URI of the graph to drop
     * @param array     $options it contains key-value pairs and should provide additional introductions for
     *                           the store and/or its adapter(s)
     *
     * @throws \Exception if given $graph is not a NamedNode
     * @throws \Exception if the given graph could not be droped
     *
     * @api
     *
     * @since 0.1
     */
    public function dropGraph(NamedNode $graph, array $options = []);
}
