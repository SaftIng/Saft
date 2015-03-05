<?php

namespace Saft\Store;

use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\Statement;
use Saft\Rdf\StatementIterator;

/**
 * Predefined sparql Store. All Triple methods reroute to the query-method. In the specific sparql-Store those
 * no longer have to be implemented, but only the Query method / SPARQL interpreter itself.
 */
abstract class AbstractSparqlStore implements StoreInterface
{
    /**
     * redirects to the query method.
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
        
        // TODO implement batching

        $query = 'INSERT DATA {'. $this->sparqlFormat($statements, $graphUri) . '}';
        
        if (is_callable($this, 'query')) {
            return $this->query($query, $options);
        } else {
            return $query;
        }
    }

    /**
     * redirects to the query method.
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
        
        $query = 'DELETE DATA {'. $this->sparqlFormat($statementIterator, $graphUri) .'}';
        
        if (is_callable($this, 'query')) {
            return $this->query($query, $options);
        } else {
            return $query;
        }
    }
    
    /**
     * redirects to the query method.
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
     * redirects to the query method.
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
     */
    public function getMatchingStatements(Statement $statement, $graphUri = null, array $options = array())
    {
        //TODO Filter Select
        $statementIterator = new ArrayStatementIteratorImpl(array($statement));
        $query = 'SELECT * WHERE {';

        $query = $query . $this->sparqlFormat($statementIterator, $graphUri) . "}";
        
        if (is_callable($this, 'query')) {
            return $this->query($query, $options);
        } else {
            return $query;
        }
        
        //throw new \Exception('getMatchingStatements Handle result');
    }

    /**
     * redirects to the query method.
     * Returns true or false depending on whether or not the statements pattern
     * has any matches in the given graph.
     *
     * @param  Statement $statement          It can be either a concrete or pattern-statement.
     * @param  string    $graphUri  optional Overrides target graph.
     * @param  array     $options   optional It contains key-value pairs and should provide additional
     *                                       introductions for the store and/or its adapter(s).
     * @return boolean Returns true if at least one match was found, false otherwise.
     */
    public function hasMatchingStatement(Statement $Statement, $graphUri = null, array $options = array())
    {
        $statementIterator = new ArrayStatementIteratorImpl(array($statement));
        $query = 'ASK {'. $this->sparqlFormat($statementIterator, $graphUri) .'}';

        if (is_callable($this, 'query')) {
            return $this->query($query, $options);
        } else {
            return $query;
        }
    }

    /**
     * Returns the Statement-Data in sparql-Format.
     *
     * @param StatementIterator $statements
     * @param string            $graphUri     Use if each statement is a triple and to use another graph as
     *                                        the default.
     * @return string, part of query
     */
    protected function sparqlFormat(StatementIterator $statements, $graphUri = null)
    {
        $query = '';
        foreach ($statements as $st) {
            if ($st instanceof Statement) {
                $con = $st->toSparqlFormat();

                $graph = $st->getGraph();
                if (null !== $graph) {
                    $con = 'Graph <'. $graph .'> {'. $con .'}';
                } elseif (null !== $graphUri) {
                    $con = 'Graph <'. $graphUri .'> {'. $con .'}';
                }

                $query .= $con .' ';
            } else {
                throw new \Exception('Not a Statement instance');
            }
        }
        return $query;
    }
}
