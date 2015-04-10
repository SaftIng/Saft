<?php

namespace Saft\Store;

use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\Statement;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\Node;
use Saft\Rdf\StatementIterator;

/**
 * Predefined sparql Store. All Triple methods reroute to the query-method. In the specific sparql-Store those
 * no longer have to be implemented, but only the Query method / SPARQL interpreter itself.
 */
abstract class AbstractSparqlStore implements StoreInterface
{
    /**
     * If set, all statement- and query related operations have to be in close collaboration with the
     * successor.
     *
     * @var instance which implements Saft\Store\StoreInterface.
     */
    protected $successor;
    
    /**
     * Adds multiple Statements to (default-) graph.
     *
     * @param  StatementIterator $statements          StatementList instance must contain Statement instances
     *                                                which are 'concret-' and not 'pattern'-statements.
     * @param  string            $graphUri   optional Overrides target graph. If set, all statements will
     *                                                be add to that graph, if available.
     * @param  array             $options    optional It contains key-value pairs and should provide additional
     *                                                introductions for the store and/or its adapter(s).
     * @return boolean Returns true, if function performed without errors. In case an error occur, an exception
     *                 will be thrown.
     * @todo implement usage of graph inside the statement(s). create groups for each graph
     */
    public function addStatements(StatementIterator $statements, $graphUri = null, array $options = array())
    {
        foreach ($statements as $st) {
            if ($st instanceof Statement && true === $st->isConcrete()) {
                // everything is fine
            
            // non-Statement instances not allowed
            } elseif (false === $st instanceof Statement) {
                throw new \Exception('addStatements does not accept non-Statement instances.');
            
            // non-concrete Statement instances not allowed
            } elseif ($st instanceof Statement && false === $st->isConcrete()) {
                throw new \Exception('At least one Statement is not concrete');
            
            } else {
                throw new \Exception('Unknown error.');
            }
        }
        
        /**
         * Create batches out of given statements to improve statement throughput, only if query function is
         * callable. Thats only the case, if this functions gets called from a implemented backend and not
         * the AbstractSparqlStore itself.
         */
        if (true === is_callable($this, 'query')) {
            $counter = 0;
            $batchSize = 100;
            $batchStatements = array();
            
            foreach ($statements as $statement) {
                // given $graphUri forces usage of it and not the graph from the statement instance
                if (null !== $graphUri) {
                    $graphUriToUse = $graphUri;
                 
                // use graphUri from statement
                } else {
                    $graphUriToUse = $statement->getGraph()->getValue();
                }
                
                if (false === isset($batchStatements[$graphUriToUse])) {
                    $batchStatements[$graphUriToUse] = new ArrayStatementIteratorImpl(array());
                }
                $batchStatements[$graphUriToUse]->append($statement);
                
                // after batch is full, execute collected statements all at once
                if (0 === $counter % $batchSize) {
                    /**
                     * $batchStatements is an array with graphUri('s) as key(s) and ArrayStatementIteratorImpl
                     * instances as value. Each entry is related to a certain graph and contains a bunch of
                     * statement instances.
                     */
                    foreach ($batchStatements as $graphUriToUse => $batch) {
                        foreach ($batch as $batchStatements) {
                            $this->query(
                                'INSERT DATA {'. $this->sparqlFormat($batchStatements, $graphUriToUse) .'}',
                                $options
                            );
                        }
                    }
                    
                    // re-init variables
                    $batchStatements = array();
                }
            }
            
        // if query function is not callable, just return generated query and use all statements given.
        } else {
            return 'INSERT DATA { '. $this->sparqlFormat($statements, $graphUri) .'}';
        }
    }

    /**
     * Removes all statements from a (default-) graph which match with given statement.
     *
     * @param  Statement $statement          It can be either a concrete or pattern-statement.
     * @param  string    $graphUri  optional Overrides target graph. If set, all statements will be delete in
     *                                       that graph.
     * @param  array     $options   optional It contains key-value pairs and should provide additional
     *                                       introductions for the store and/or its adapter(s).
     * @return boolean Returns true, if function performed without errors. In case an error occur, an exception
     *                 will be thrown.
     */
    public function deleteMatchingStatements(Statement $statement, $graphUri = null, array $options = array())
    {
        $statementIterator = new ArrayStatementIteratorImpl(array($statement));
        
        $query = 'DELETE DATA { '. $this->sparqlFormat($statementIterator, $graphUri) .'}';
        return $this->query($query, $options);
    }
    
    /**
     * Returns array with graphUri's which are available.
     *
     * @return array Array which contains graph URI's as values and keys.
     */
    public function getAvailableGraphs()
    {
        $result = $this->query('SELECT DISTINCT ?g WHERE { GRAPH ?g {?s ?p ?o.} }');
        
        $graphs = array();

        foreach ($result as $entry) {
            $graphs[$entry['g']] = $entry['g'];
        }
        
        return $graphs;
    }

    /**
     * It gets all statements of a given graph which match the following conditions:
     * - statement's subject is either equal to the subject of the same statement of the graph or it is null.
     * - statement's predicate is either equal to the predicate of the same statement of the graph or it is null.
     * - statement's object is either equal to the object of a statement of the graph or it is null.
     *
     * @param  Statement $statement          It can be either a concrete or pattern-statement.
     * @param  string    $graphUri  optional Overrides target graph. If set, you will get all
     *                                       matching statements of that graph.
     * @param  array     $options   optional It contains key-value pairs and should provide additional
     *                                       introductions for the store and/or its adapter(s).
     * @return StatementIterator It contains Statement instances  of all matching
     *                           statements of the given graph.
     * @todo FILTER select
     * @todo check if graph URI is valid
     */
    public function getMatchingStatements(Statement $statement, $graphUri = null, array $options = array())
    {
        $statementIterator = new ArrayStatementIteratorImpl(array($statement));
        
        return $this->query(
            'SELECT * WHERE { '. $this->sparqlFormat($statementIterator, $graphUri) .'}',
            $options
        );
    }

    /**
     * Returns true or false depending on whether or not the statements pattern
     * has any matches in the given graph.
     *
     * @param  Statement $statement          It can be either a concrete or pattern-statement.
     * @param  string    $graphUri  optional Overrides target graph.
     * @param  array     $options   optional It contains key-value pairs and should provide additional
     *                                       introductions for the store and/or its adapter(s).
     * @return boolean Returns true if at least one match was found, false otherwise.
     */
    public function hasMatchingStatement(Statement $statement, $graphUri = null, array $options = array())
    {
        $statementIterator = new ArrayStatementIteratorImpl(array($statement));
        $result = $this->query('ASK { '. $this->sparqlFormat($statementIterator, $graphUri) .'}', $options);
        
        if (true === is_object($result)) {
            return $result->getResultObject();
        } else {
            return $result;
        }
    }
    
    /**
     * Set successor instance. This method is useful, if you wanna build chain of instances which implement
     * StoreInterface. It sets another instance which will be later called, if a statement- or query-related
     * function gets called.
     * E.g. you chain a query cache and a virtuoso instance. In this example all queries will be handled by
     * the query cache first, but if no cache entry was found, the virtuoso instance gets called.
     *
     * @return array Array which contains information about the store and its features.
     */
    public function setChainSuccessor(StoreInterface $successor)
    {
        $this->successor = $successor;
    }

    /**
     * Returns the Statement-Data in sparql-Format.
     *
     * @param StatementIterator $statements   List of statements to format as SPARQL string.
     * @param string            $graphUri     Use if each statement is a triple and to use another graph as
     *                                        the default.
     * @return string, part of query
     */
    protected function sparqlFormat(StatementIterator $statements, $graphUri = null)
    {
        $query = '';
        foreach ($statements as $statement) {
            if ($statement instanceof Statement) {
                $con = $this->getNodeInSparqlFormat($statement->getSubject()) . ' ' .
                    $this->getNodeInSparqlFormat($statement->getPredicate()) . ' ' .
                    $this->getNodeInSparqlFormat($statement->getObject());

                if (null !== $graphUri && true === is_string($graphUri)) {
                    // check if its a valid URI
                    if (true === NamedNodeImpl::check($graphUri)) {
                        $sparqlString = 'Graph <'. $graphUri .'> {' . $con .'}';
                    
                    // check for variable, which has a ? as first char
                    } elseif ('?' == substr($graphUri, 0, 1)) {
                        $sparqlString = 'Graph '. $graphUri .' {' . $con .'}';
                        
                    // invalid $graphUri
                    } else {
                        throw new \Exception('Parameter $graphUri is neither a valid URI nor variable.');
                    }
                } else {
                    $sparqlString = $statement->toSparqlFormat();
                }
                
                $query .= $sparqlString .' ';
            } else {
                throw new \Exception('Not a Statement instance');
            }
        }
        return $query;
    }

    /**
     * Returns given Node instance in SPARQL format.
     *
     * @param  Node   $node Node instance to format.
     * @return string       Either NQuad notation (if node is concrete) or string representation of given node.
     */
    protected function getNodeInSparqlFormat(Node $node)
    {
        if (true === $node->isConcrete()) {
            return $node->toNQuads();
        } else {
            return $node->__toString();
        }
    }
}
