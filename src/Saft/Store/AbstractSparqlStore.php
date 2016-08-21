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
use Saft\Sparql\Result\Result;
use Saft\Sparql\Result\EmptyResult;
use Saft\Sparql\Result\ResultFactory;

/**
 * Predefined SPARQL store. All Triple methods reroute to the query-method. In the specific sparql-Store those
 * no longer have to be implemented, but only the Query method / SPARQL interpreter itself.
 *
 * @api
 * @since 0.1
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
     * Constructor.
     *
     * @param NodeFactory              $nodeFactory Instance of NodeFactory.
     * @param StatementFactory         $statementFactory Instance of StatementFactory.
     * @param QueryFactory             $queryFactory Instance of QueryFactory.
     * @param ResultFactory            $resultFactory Instance of ResultFactory.
     * @param StatementIteratorFactory $statementIteratorFactory Instance of StatementIteratorFactory.
     * @api
     * @since 0.1
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
     * @param StatementIterator|array $statements StatementList instance must contain Statement instances which
     *                                            are 'concret-' and not 'pattern'-statements.
     * @param Node                    $graph      Overrides target graph. If set, all statements will be add to
     *                                            that graph, if it is available. (optional)
     * @param array                   $options    Key-value pairs which provide additional introductions for the
     *                                            store and/or its adapter(s). (optional)
     * @api
     * @since 0.1
     */
    public function addStatements($statements, Node $graph = null, array $options = array())
    {
        $graphUriToUse = null;

        /**
         * Create batches out of given statements to improve statement throughput.
         */
        $counter = 1;
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

            // use graph from statement
            } elseif (null !== $statement->getGraph()) {
                $graphUriToUse = $statement->getGraph()->getUri();

            } else {
                $graphUriToUse = null;
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
                    $content = '';

                    foreach ($batch as $batchEntries) {
                        $content .= $this->sparqlFormat(
                            $this->statementIteratorFactory->createStatementIteratorFromArray(array($batchEntries)),
                            $graph
                        ) .' ';
                    }

                    $this->query('INSERT DATA {'. $content .'}', $options);
                }

                // re-init variables
                $batchStatements = array();

            } else {
                ++$counter;
            }
        }

        // handle remaining statements of the batch (that happens if the batch size was not reached (again))
        // TODO remove this code duplication. Maybe move the repeeted code to a new method
        $content = '';

        foreach ($batchStatements as $graphUriToUse => $batch) {
            foreach ($batch as $batchEntries) {
                $content .= $this->sparqlFormat(
                    $this->statementIteratorFactory->createStatementIteratorFromArray(array($batchEntries)),
                    $graph
                ) .' ';
            }
        }

        // if remaining statements are available
        if (0 < count($batchStatements)) {
            $this->query('INSERT DATA {'. $content .'}', $options);
        }
    }

    /**
     * Create a new graph with the URI given as Node. If the underlying store implementation doesn't
     * support empty graphs this method will have no effect.
     *
     * @param NamedNode $graph   Instance of NamedNode containing the URI of the graph to create.
     * @param array     $options It contains key-value pairs and should provide additional introductions for the
     *                           store and/or its adapter(s). (optional)
     * @throws \Exception if given $graph is not a NamedNode.
     * @throws \Exception if the given graph could not be created.
     * @api
     * @since 0.1
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
     * @param Statement $statement It can be either a concrete or pattern-statement.
     * @param Node      $graph     Overrides target graph. If set, all statements will be delete in that
     *                             graph. (optional)
     * @param array     $options   Key-value pairs which provide additional introductions for the store and/or its
     *                             adapter(s). (optional)
     * @api
     * @since 0.1
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

        }

        $statementIterator = $this->statementIteratorFactory->createStatementIteratorFromArray(
            array($statement)
        );

        $this->query('DELETE WHERE { '. $this->sparqlFormat($statementIterator, $graph) .'}', $options);
    }

    /**
     * Removes the given graph from the store.
     *
     * @param NamedNode  $graph   Instance of NamedNode containing the URI of the graph to drop.
     * @param array      $options It contains key-value pairs and should provide additional introductions for the
     *                            store and/or its adapter(s). (optional)
     * @throws \Exception if given $graph is not a NamedNode.
     * @throws \Exception if the given graph could not be droped
     * @api
     * @since 0.1
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
     * @api
     * @since 0.1
     */
    public function getGraphs()
    {
        $result = $this->query('SELECT DISTINCT ?g WHERE { GRAPH ?g {?s ?p ?o.} }');

        $graphs = array();

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
     * @param Statement $statement It can be either a concrete or pattern-statement.
     * @param Node      $graph     Overrides target graph. If set, you will get all matching statements of that
     *                             graph. (optional)
     * @param array     $options   It contains key-value pairs and should provide additional introductions
     *                             for the store and/or its adapter(s). (optional)
     * @return StatementIterator It contains Statement instances  of all matching statements of the given graph.
     * @api
     * @since 0.1
     * @todo check if graph URI is valid
     * @todo make it possible to read graphUri from $statement, if given $graphUri is null
     */
    public function getMatchingStatements(Statement $statement, Node $graph = null, array $options = array())
    {
        // otherwise check, if graph was set in the statement and it is a named node and use it, if so.
        if (null === $graph && $statement->isQuad()) {
            $graph = $statement->getGraph();
        }

        /*
         * Build query
         */
        $query = 'SELECT ?s ?p ?o { ?s ?p ?o ';
        if ($graph !== null) {
            $query = 'SELECT ?s ?p ?o ?g { graph ?g { ?s ?p ?o } ';
        }

        // create shortcuts for S, P and O
        $subject = $statement->getSubject();
        $predicate = $statement->getPredicate();
        $object = $statement->getObject();

        // add filter, if subject is a named node or literal
        if (!$subject->isPattern()) {
            $query .= 'FILTER (?s = '. $subject->toNQuads() .') ';
        }

        // add filter, if predicate is a named node or literal
        if (!$predicate->isPattern()) {
            $query .= 'FILTER (?p = '. $predicate->toNQuads() .') ';
        }

        // add filter, if object is a named node or literal
        if (!$object->isPattern()) {
            $query .= 'FILTER (?o = '. $object->toNQuads() .') ';
        }

        // add filter, if graph is a named node or literal
        if ($graph !== null && !$graph->isPattern()) {
            $query .= 'FILTER (?g = '. $graph->toNQuads() .') ';
        }

        $query .= '}';

        // execute query and save result
        // TODO transform getMatchingStatements into lazy loading, so a batch loading is possible
        $result = $this->query($query, $options);

        /*
         * Transform SetResult entries to Statement instances.
         */
        $statementList = array();
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
     * Returns true or false depending on whether or not the statements pattern has any matches in the given graph.
     *
     * @param Statement $statement It can be either a concrete or pattern-statement.
     * @param Node      $graph     Overrides target graph. (optional)
     * @param array     $options   It contains key-value pairs and should provide additional
     *                             introductions for the store and/or its adapter(s). (optional)
     * @return boolean Returns true if at least one match was found, false otherwise.
     * @api
     * @since 0.1
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

        $statementIterator = $this->statementIteratorFactory->createStatementIteratorFromArray(
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
     * @param StatementIterator $statements List of statements to format as SPARQL string.
     * @param string            $graphUri   Use if each statement is a triple and to use another graph as
     *                                      the default.
     * @return string, part of query
     * @api
     * @since 0.1
     */
    protected function sparqlFormat(StatementIterator $statements, Node $graph = null)
    {
        $sparqlUtils = new SparqlUtils();
        return $sparqlUtils->statementIteratorToSparqlFormat($statements, $graph);
    }

    /**
     * Helper function which transforms an result entry to its proper Node instance.
     *
     * @param array $entry
     * @return Node Instance of Node.
     * @unstable
     */
    public function transformEntryToNode($entry)
    {
        /*
         * An $entry looks like:
         * array(
         *      'type' => 'uri',
         *      'value' => '...'
         * )
         */

        // it seems that for instance Virtuoso returns type=literal for bnodes,
        // so we manually fix that here to avoid that problem if other stores act
        // the same
        if (true === is_string($entry['value']) && false !== strpos($entry['value'], '_:')) {
            $entry['type'] = 'bnode';
        }

        $newEntry = null;

        switch ($entry['type']) {
            /**
             * Literal (language'd)
             */
            case 'literal':
                $lang = null;
                if (isset($entry['xml:lang'])) {
                    $lang = $entry['xml:lang'];
                }

                $newEntry = $this->nodeFactory->createLiteral(
                    $entry['value'],
                    'http://www.w3.org/1999/02/22-rdf-syntax-ns#langString',
                    $lang
                );

                break;

            /**
             * Typed-Literal
             */
            case 'typed-literal':
                $newEntry = $this->nodeFactory->createLiteral($entry['value'], $entry['datatype']);
                break;

            /**
             * NamedNode
             */
            case 'uri':
                $newEntry = $this->nodeFactory->createNamedNode($entry['value']);
                break;

            /**
             * BlankNode
             */
            case 'bnode':
                $newEntry = $this->nodeFactory->createBlankNode($entry['value']);
                break;

            default:
                throw new \Exception('Unknown type given: ' . $entry['type']);
                break;
        }

        return $newEntry;
    }
}
