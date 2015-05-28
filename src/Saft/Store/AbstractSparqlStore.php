<?php

namespace Saft\Store;

use Saft\Rdf\NamedNode;
use Saft\Rdf\Node;
use Saft\Rdf\NodeFactory;
use Saft\Rdf\Statement;
use Saft\Rdf\StatementFactory;
use Saft\Rdf\StatementIterator;
use Saft\Rdf\StatementIteratorFactory;
use Saft\Sparql\SparqlUtils;
use Saft\Sparql\Query\QueryFactory;
use Saft\Store\Result\Result;
use Saft\Store\Result\EmptyResult;
use Saft\Store\Result\ResultFactory;

/**
 * Predefined sparql Store. All Triple methods reroute to the query-method. In the specific sparql-Store those
 * no longer have to be implemented, but only the Query method / SPARQL interpreter itself.
 */
abstract class AbstractSparqlStore implements Store
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
     * @var ResultFactory
     */
    private $resultFactory;

    /**
     * @var StatementFactory
     */
    private $statementFactory;

    /**
     * @var StatementIteratorFactory
     */
    private $statementIteratorFactory;

    /**
     * @param NodeFactory              $nodeFactory
     * @param StatementFactory         $statementFactory
     * @param QueryFactory             $queryFactory
     * @param ResultFactory            $resultFactory
     * @param StatementIteratorFactory $statementIteratorFactory
     */
    public function __construct(
        NodeFactory $nodeFactory,
        StatementFactory $statementFactory,
        QueryFactory $queryFactory,
        ResultFactory $resultFactory,
        StatementIteratorFactory $statementIteratorFactory
    ) {
        $this->nodeFactory = $nodeFactory;
        $this->statementFactory = $statementFactory;
        $this->queryFactory = $queryFactory;
        $this->resultFactory = $resultFactory;
        $this->statementIteratorFactory = $statementIteratorFactory;
    }

    /**
     * Adds multiple Statements to (default-) graph.
     *
     * @param  StatementIterator|array $statements       StatementList instance must contain Statement
     *                                                   instances which are 'concret-' and not
     *                                                   'pattern'-statements.
     * @param  Node                    $graph   optional Overrides target graph. If set, all statements
     *                                                   will be add to that graph, if it is available.
     * @param  array                   $options optional Key-value pairs which provide additional
     *                                                   introductions for the store and/or its
     *                                                   adapter(s).
     */
    public function addStatements($statements, Node $graph = null, array $options = array())
    {
        $graphUriToUse = null;

        /**
         * Create batches out of given statements to improve statement throughput.
         */
        $counter = 0;
        $batchSize = 100;
        $batchStatements = array();

        foreach ($statements as $statement) {
            // non-concrete Statement instances not allowed
            if (false === $statement->isConcrete()) {
                // We would need a rollback here, but we don't have any transactions so far
                throw new \Exception('At least one Statement is not concrete');
            }

            // given $graph forces usage of it and not the graph from the statement instance
            if (null !== $graph) {
                $graphUriToUse = $graph->getUri();
                // reuse $graph instance later on.

            // use graphUri from statement
            } elseif (null !== $statement->getGraph()) {
                $graph = $statement->getGraph();
                $graphUriToUse = $graph->getUri();

            // no graph instance was found
            } else {
                throw new \Exception('Graph was not given, neither as parameter nor in statement.');
            }

            // init batch entry for the current graph URI, if not set yet.
            if (false === isset($batchStatements[$graphUriToUse])) {
                $batchStatements[$graphUriToUse] = array();
            }

            $batchStatements[$graphUriToUse][] = $statement;

            // after batch is full, execute collected statements all at once
            if (0 === $counter % $batchSize) {
                /**
                 * $batchStatements is an array with graphUri('s) as key(s) and iterator instances as value.
                 * Each entry is related to a certain graph and contains a bunch of statement instances.
                 */
                foreach ($batchStatements as $graphUriToUse => $batch) {
                    foreach ($batch as $batchEntries) {
                        $content = $this->sparqlFormat(
                            $this->statementIteratorFactory->createArrayStatementIterator(
                                array($batchEntries)
                            ),
                            $graph
                        );

                        $this->query('INSERT DATA {'. $content .'}', $options);
                    }
                }

                // re-init variables
                $batchStatements = array();
            }
        }
    }

    /**
     * Create a new graph with the URI given as Node. If the underlying store implementation doesn't
     * support empty graphs this method will have no effect.
     *
     * @param  NamedNode  $graph            Instance of NamedNode containing the URI of the graph to create.
     * @param  array      $options optional It contains key-value pairs and should provide additional
     *                                      introductions for the store and/or its adapter(s).
     * @throws \Exception If given $graph is not a NamedNode.
     * @throws \Exception If the given graph could not be created.
     */
    public function createGraph(NamedNode $graph, array $options = array())
    {
        if ($graph->isNamed()) {
            $this->query('CREATE SILENT GRAPH <'. $graph->getUri() .'>');
        } else {
            throw new \Exception('Given $graph is not a NamedNode.');
        }
    }

    /**
     * Removes all statements from a (default-) graph which match with given statement.
     *
     * @param  Statement $statement          It can be either a concrete or pattern-statement.
     * @param  Node      $graph     optional Overrides target graph. If set, all statements will
     *                                       be delete in that graph.
     * @param  array     $options   optional Key-value pairs which provide additional introductions
     *                                       for the store and/or its adapter(s).
     */
    public function deleteMatchingStatements(Statement $statement, Node $graph = null, array $options = array())
    {
        // given $graph forces usage of it and not the graph from the statement instance
        if (null !== $graph) {
            $graphUriToUse = $graph->getUri();

        // use graphUri from statement
        } elseif (null !== $statement->getGraph()) {
            $graph = $statement->getGraph();
            $graphUriToUse = $graph->getUri();

        } else {
            throw new \Exception('Graph was not given, neither as parameter nor in statement.');
        }

        $statementIterator = $this->statementIteratorFactory->createArrayStatementIterator(
            array($statement)
        );

        $this->query('DELETE WHERE { '. $this->sparqlFormat($statementIterator, $graph) .'}', $options);
    }

    /**
     * Removes the given graph from the store.
     *
     * @param  NamedNode  $graph            Instance of NamedNode containing the URI of the graph to drop.
     * @param  array      $options optional It contains key-value pairs and should provide additional
     *                                      introductions for the store and/or its adapter(s).
     * @throws \Exception If given $graph is not a NamedNode.
     * @throws \Exception If the given graph could not be droped
     */
    public function dropGraph(NamedNode $graph, array $options = array())
    {
        if ($graph->isNamed()) {
            $this->query('DROP SILENT GRAPH <'. $graph->getUri() .'>');
        } else {
            throw new \Exception('Given $graph is not a NamedNode.');
        }
    }

    /**
     * Returns a list of all available graph URIs of the store. It can also respect access control,
     * to only returned available graphs in the current context. But that depends on the implementation
     * and can differ.
     *
     * @return array Simple array of key-value-pairs, which consists of graph URIs as key and NamedNode
     *               instance as value.
     */
    public function getAvailableGraphs()
    {
        $result = $this->query('SELECT DISTINCT ?g WHERE { GRAPH ?g {?s ?p ?o.} }');

        $graphs = array();

        foreach ($result as $entry) {
            $graphs[$entry['g']->getUri()] = $entry['g'];
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
     * @param  Statement         $Statement          It can be either a concrete or pattern-statement.
     * @param  Node              $graph     optional Overrides target graph. If set, you will get all
     *                                               matching statements of that graph.
     * @param  array             $options   optional It contains key-value pairs and should provide
     *                                               additional introductions for the store and/or its
     *                                               adapter(s).
     * @return StatementIterator It contains Statement instances  of all matching statements of the
     *                           given graph.
     * @todo check if graph URI is valid
     * @todo make it possible to read graphUri from $statement, if given $graphUri is null
     */
    public function getMatchingStatements(Statement $statement, Node $graph = null, array $options = array())
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

        /*
         * Build query
         */
        $query = 'SELECT ?s ?p ?o ';
        if (null !== $graph) {
            $query .= 'FROM <'. $graph->getUri() .'> ';
        }
        $query .= 'WHERE { ?s ?p ?o ';

        // create shortcuts for S, P and O
        $subject = $statement->getSubject();
        $predicate = $statement->getPredicate();
        $object = $statement->getObject();

        // add filter, if subject is a named node or literal
        if ($subject->isNamed()) {
            $query .= 'FILTER (str(?s) = "'. $subject->getUri() .'") ';
        }

        // add filter, if predicate is a named node or literal
        if ($predicate->isNamed()) {
            $query .= 'FILTER (str(?p) = "'. $predicate->getUri() .'") ';
        }

        // add filter, if object is a named node or literal
        if ($object->isNamed()) {
            $query .= 'FILTER (str(?o) = "'. $object->getUri() .'") ';
        } elseif ($object->isLiteral()) {
            $query .= 'FILTER (str(?o) = "'. $object->getValue() .'") ';
        }
        $query .= '}';

        // execute query and save result
        // TODO transform getMatchingStatements into lazy loading, so a batch loading is possible
        $result = $this->query($query, $options);

        if (null === $result) {
            return $this->resultFactory->createEmptyResult();
        }

        /*
         * Transform SetResult entries to Statement instances.
         */
        $entries = array();
        foreach ($result as $entry) {
            $statementList = array();
            $i = 0;
            foreach ($result->getVariables() as $variable) {
                $statementList[$i++] = $entry[$variable];
            }
            $entries[] = $this->statementFactory->createStatement(
                $statementList[0],
                $statementList[1],
                $statementList[2]
            );
        }

        // returns an iterator containing received statements
        return $this->statementIteratorFactory->createArrayStatementIterator($entries);
    }

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
    public function hasMatchingStatement(Statement $statement, Node $graph = null, array $options = array())
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

        $statementIterator = $this->statementIteratorFactory->createArrayStatementIterator(
            array($statement)
        );

        $result = $this->query('ASK { '. $this->sparqlFormat($statementIterator, $graph) .'}', $options);

        if (true === is_object($result)) {
            return $result->getValue();
        } else {
            return $result;
        }
    }

    /**
     * Returns the Statement-Data in sparql-Format.
     *
     * @param StatementIterator $statements   List of statements to format as SPARQL string.
     * @param string            $graphUri     Use if each statement is a triple and to use another graph as
     *                                        the default.
     * @return string, part of query
     */
    protected function sparqlFormat(StatementIterator $statements, Node $graph = null)
    {
        return SparqlUtils::statementIteratorToSparqlFormat($statements, $graph);
    }
}
