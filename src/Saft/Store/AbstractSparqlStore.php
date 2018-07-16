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
use Saft\Rdf\RdfHelpers;
use Saft\Rdf\Statement;
use Saft\Rdf\StatementFactory;
use Saft\Rdf\StatementIterator;
use Saft\Rdf\StatementIteratorFactory;
use Saft\Sparql\Result\Result;
use Saft\Sparql\Result\ResultFactory;

/**
 * Predefined SPARQL store. All Triple methods reroute to the query-method. In the specific sparql-Store those
 * no longer have to be implemented, but only the query method / SPARQL interpreter itself.
 *
 * @api
 *
 * @since 0.1
 */
abstract class AbstractSparqlStore implements Store
{
    /**
     * @var NodeFactory
     */
    protected $nodeFactory;

    /**
     * @var ResultFactory
     */
    protected $resultFactory;

    /**
     * @var StatementFactory
     */
    protected $statementFactory;

    /**
     * @var StatementIteratorFactory
     */
    protected $statementIteratorFactory;

    /**
     * Constructor.
     *
     * @param NodeFactory              $nodeFactory              instance of NodeFactory
     * @param StatementFactory         $statementFactory         instance of StatementFactory
     * @param QueryFactory             $queryFactory             instance of QueryFactory
     * @param ResultFactory            $resultFactory            instance of ResultFactory
     * @param StatementIteratorFactory $statementIteratorFactory instance of StatementIteratorFactory
     * @param RdfHelpers               $rdfHelpers
     *
     * @api
     *
     * @since 0.1
     */
    public function __construct(
        NodeFactory $nodeFactory,
        StatementFactory $statementFactory,
        ResultFactory $resultFactory,
        StatementIteratorFactory $statementIteratorFactory,
        RdfHelpers $rdfHelpers
    ) {
        $this->nodeFactory = $nodeFactory;
        $this->statementFactory = $statementFactory;
        $this->resultFactory = $resultFactory;
        $this->statementIteratorFactory = $statementIteratorFactory;
        $this->rdfHelpers = $rdfHelpers;
    }

    /**
     * Adds multiple Statements to (default-) graph.
     *
     * @param StatementIterator|array $statements statementList instance must contain Statement instances which
     *                                            are 'concret-' and not 'pattern'-statements
     * @param Node                    $graph      Overrides target graph. If set, all statements will be add to
     *                                            that graph, if it is available. (optional)
     * @param array                   $options    Key-value pairs which provide additional introductions for the
     *                                            store and/or its adapter(s). (optional)
     *
     * @api
     *
     * @since 0.1
     */
    public function addStatements(iterable $statements, Node $graph = null, array $options = [])
    {
        $graphUriToUse = null;

        /**
         * Create batches out of given statements to improve statement throughput.
         */
        $counter = 1;
        $batchSize = 100;
        $batchStatements = [];

        foreach ($statements as $statement) {
            // non-concrete Statement instances not allowed
            if (false === $statement->isConcrete()) {
                // We would need a rollback here, but we don't have any transactions so far
                throw new \Exception('At least one Statement is not concrete: '.$statement->toNTriples());
            }

            // given $graph forces usage of it and not the graph from the statement instance
            if ($graph instanceof NamedNode) {
                $graphUriToUse = $graph->getUri();
            // use graph from statement
            } elseif ($statement->getGraph() instanceof NamedNode) {
                $graphUriToUse = $statement->getGraph()->getUri();
            // no graph, therefore store decides
            } else {
                $graphUriToUse = null;
            }

            // init batch entry for the current graph URI, if not set yet.
            if (false === isset($batchStatements[$graphUriToUse])) {
                $batchStatements[$graphUriToUse] = [];
            }

            $batchStatements[$graphUriToUse][] = $statement;
        }

        /**
         * $batchStatements is an array with graphUri('s) as key(s) and iterator instances as value.
         * Each entry is related to a certain graph and contains a bunch of statement instances.
         */
        foreach ($batchStatements as $graphUriToUse => $batch) {
            $content = '';

            $graph = null;
            if (null !== $graphUriToUse) {
                $graph = $this->nodeFactory->createNamedNode($graphUriToUse);
            }

            foreach ($batch as $batchEntries) {
                $content .= $this->sparqlFormat(
                    $this->statementIteratorFactory->createStatementIteratorFromArray([$batchEntries]),
                    $graph
                ).' ';
            }

            $this->query('INSERT DATA {'.$content.'}', $options);
        }
    }

    /**
     * Create a new graph with the URI given as Node. If the underlying store implementation doesn't
     * support empty graphs this method will have no effect.
     *
     * @param NamedNode $graph   instance of NamedNode containing the URI of the graph to create
     * @param array     $options It contains key-value pairs and should provide additional introductions for the
     *                           store and/or its adapter(s). (optional)
     *
     * @throws \Exception if given $graph is not a NamedNode
     * @throws \Exception if the given graph could not be created
     *
     * @api
     *
     * @since 0.1
     */
    public function createGraph(NamedNode $graph, array $options = [])
    {
        $this->query('CREATE SILENT GRAPH <'.$graph->getUri().'>');
    }

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
    public function deleteMatchingStatements(Statement $statement, Node $graph = null, array $options = [])
    {
        // given $graph forces usage of it and not the graph from the statement instance
        if (null !== $graph) {
            $graphUriToUse = $graph->getUri();

        // use graphUri from statement
        } elseif (null !== $statement->getGraph()) {
            $graph = $statement->getGraph();
            $graphUriToUse = $graph->getUri();
        }

        $statementIterator = $this->statementIteratorFactory->createStatementIteratorFromArray(
            [$statement]
        );

        $this->query('DELETE WHERE { '.$this->sparqlFormat($statementIterator, $graph).'}', $options);
    }

    /**
     * Removes the given graph from the store.
     *
     * @param NamedNode $graph   instance of NamedNode containing the URI of the graph to drop
     * @param array     $options It contains key-value pairs and should provide additional introductions for the
     *                           store and/or its adapter(s). (optional)
     *
     * @throws \Exception if given $graph is not a NamedNode
     * @throws \Exception if the given graph could not be droped
     *
     * @api
     *
     * @since 0.1
     */
    public function dropGraph(NamedNode $graph, array $options = [])
    {
        $this->query('DROP SILENT GRAPH <'.$graph->getUri().'>');
    }

    /**
     * Returns a list of all available graph URIs of the store. It can also respect access control,
     * to only returned available graphs in the current context. But that depends on the implementation
     * and can differ.
     *
     * @return iterable simple array of key-value-pairs, which consists of graph URIs as key and NamedNode
     *                  instance as value
     *
     * @api
     *
     * @since 0.1
     */
    public function getGraphs(): iterable
    {
        $result = $this->query('SELECT DISTINCT ?g WHERE { GRAPH ?g {?s ?p ?o.} }');

        $graphs = [];

        foreach ($result as $entry) {
            $graphNode = $entry['g'];
            $graphs[$graphNode->getUri()] = $graphNode;
        }

        return $graphs;
    }

    /**
     * It gets all statements of a given graph which match the following conditions:
     * - statement's subject is either equal to the subject of the same statement of the graph or
     *   it is null.
     * - statement's predicate is either equal to the predicate of the same statement of the graph or
     *   it is null.
     * - statement's object is either equal to the object of a statement of the graph or it is null.
     *
     * @param Statement $statement it can be either a concrete or pattern-statement
     * @param Node      $graph     Overrides target graph. If set, you will get all matching statements of that
     *                             graph. (optional)
     * @param array     $options   It contains key-value pairs and should provide additional introductions
     *                             for the store and/or its adapter(s). (optional)
     *
     * @return StatementIterator it contains Statement instances  of all matching statements of the given graph
     *
     * @api
     *
     * @since 0.1
     *
     * @todo check if graph URI is valid
     * @todo make it possible to read graphUri from $statement, if given $graphUri is null
     */
    public function getMatchingStatements(Statement $statement, Node $graph = null, array $options = []): StatementIterator
    {
        // otherwise check, if graph was set in the statement and it is a named node and use it, if so.
        if (null === $graph && $statement->isQuad()) {
            $graph = $statement->getGraph();
        }

        /*
         * Build query
         */
        if ($graph !== null) {
            $query = 'SELECT ?s ?p ?o ?g WHERE { graph ?g { ?s ?p ?o } ';
        } else {
            $query = 'SELECT ?s ?p ?o WHERE { ?s ?p ?o ';
        }

        // create shortcuts for S, P and O
        $subject = $statement->getSubject();
        $predicate = $statement->getPredicate();
        $object = $statement->getObject();

        // add filter, if subject is a named node or literal
        if (!$subject->isPattern()) {
            $query .= 'FILTER (?s = '.$subject->toNQuads().') ';
        }

        // add filter, if predicate is a named node or literal
        if (!$predicate->isPattern()) {
            $query .= 'FILTER (?p = '.$predicate->toNQuads().') ';
        }

        // add filter, if object is a named node or literal
        if (!$object->isPattern()) {
            $query .= 'FILTER (?o = '.$object->toNQuads().') ';
        }

        // add filter, if graph is a named node or literal
        if ($graph !== null && !$graph->isPattern()) {
            $query .= 'FILTER (?g = '.$graph->toNQuads().') ';
        }

        $query .= '}';

        // execute query and save result
        $result = $this->query($query, $options);

        /*
         * Transform SetResult entries to Statement instances.
         */
        $statementList = [];
        if ($graph !== null) {
            foreach ($result as $entry) {
                $statementList[] = $this->statementFactory->createStatement(
                    $entry['s'],
                    $entry['p'],
                    $entry['o'],
                    $entry['g']
                );
            }
        } else {
            foreach ($result as $entry) {
                $statementList[] = $this->statementFactory->createStatement(
                    $entry['s'],
                    $entry['p'],
                    $entry['o']
                );
            }
        }

        // return a StatementIterator which contains the matching statements
        return $this->statementIteratorFactory->createStatementIteratorFromArray($statementList);
    }

    /**
     * Determines the type of a given query in a very basic way.
     *
     * Returns either:
     * - construct,
     * - select,
     * - insert-data
     * - update-data
     * - null, if unknown
     *
     * @param string $query
     *
     * @return string|null
     *
     * @unstable
     *
     * @since 2.0.0
     */
    public function getQueryType(string $query): ?string
    {
        // remove PREFIX entries at the beginning
        $query = \preg_replace('/PREFIX\s+[a-z]+:\s*<.*?>/im', '', $query);

        $query = \ltrim(\strtolower($query));

        // CONSTRUCT
        if ('construct' == \substr($query, 0, 9)) {
            return 'construct';

        // SELECT
        } elseif ('select' == \substr($query, 0, 6)) {
            return 'select';

        // ASK
        } elseif ('ask' == \substr($query, 0, 3)) {
            return 'ask';

        // INSERT DATA
        } elseif ('insert data' == \substr($query, 0, 11)) {
            return 'insert-data';

        // DELETE DATA
        } elseif ('delete data' == \substr($query, 0, 11)) {
            return 'delete-data';
        }

        return null;
    }

    /**
     * Returns true or false depending on whether or not the statements pattern has any matches in the given graph.
     *
     * @param Statement $statement it can be either a concrete or pattern-statement
     * @param Node      $graph     Overrides target graph. (optional)
     * @param array     $options   It contains key-value pairs and should provide additional
     *                             introductions for the store and/or its adapter(s). (optional)
     *
     * @return bool returns true if at least one match was found, false otherwise
     *
     * @api
     *
     * @since 0.1
     */
    public function hasMatchingStatement(Statement $statement, Node $graph = null, array $options = []): bool
    {
        // if $graph was given, but its not a named node, set it to null.
        if (null !== $graph && false === $graph->isNamed()) {
            $graph = null;
        }
        // otherwise check, if graph was set in the statement and it is a named node and use it, if so.
        if (null === $graph
            && null !== $statement->getGraph()
            && true === $statement->getGraph()->isNamed()) {
            $graph = $statement->getGraph();
        }

        $statementIterator = $this->statementIteratorFactory->createStatementIteratorFromArray(
            [$statement]
        );

        $result = $this->query('ASK { '.$this->sparqlFormat($statementIterator, $graph).'}', $options);

        if (true === is_object($result)) {
            return $result->getValue();
        } else {
            return $result;
        }
    }

    /**
     * Returns the Statement-Data in sparql-Format.
     *
     * @param StatementIterator $statements list of statements to format as SPARQL string
     * @param string            $graphUri   use if each statement is a triple and to use another graph as
     *                                      the default
     *
     * @return string Statements usable in a SPARQL query.
     *
     * @api
     *
     * @since 0.1
     */
    protected function sparqlFormat(StatementIterator $statements, Node $graph = null): string
    {
        $query = '';
        foreach ($statements as $statement) {
            if ($statement instanceof Statement) {
                $con = $statement->getSubject()->toNQuads().' '.
                       $statement->getPredicate()->toNQuads().' '.
                       $statement->getObject()->toNQuads().' . ';

                // determine target graph
                $graphToUse = $graph;
                if ($graph == null && $statement->isQuad()) {
                    $graphToUse = $statement->getGraph();
                }

                if (null !== $graphToUse) {
                    $sparqlString = 'Graph '.$graphToUse->toNQuads().' {'.$con.'}';
                } else {
                    $sparqlString = $con;
                }
                $query .= $sparqlString.' ';
            } else {
                throw new \Exception('Parameter $statements contains at least one non-Statement instance.');
            }
        }
        return $query;
    }
}
