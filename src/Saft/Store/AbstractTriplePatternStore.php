<?php

namespace Saft\Store;

use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\AbstractNamedNode;
use Saft\Rdf\StatementImpl;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\LiteralImpl;
use Saft\Rdf\VariableImpl;
use Saft\Sparql\Query\AbstractQuery;
use Saft\Sparql\Query\Query;

/**
 * Predefined Pattern-statement Store. The Triple-methods need to be implemented in the specific statement-store.
 * The query method is defined in the abstract class and reroute to the triple-methods.
 */
abstract class AbstractTriplePatternStore implements StoreInterface
{
    /**
     * @param  string     $query            SPARQL query string.
     * @param  string     $options optional Further configurations.
     * @throws \Exception                   If unsupported query was given
     * @throws \Exception                   If WITH-DELETE-WHERE and WITH-DELETE-INSERT-WHERE query was given.
     */
    public function query($query, array $options = array())
    {
        $queryObject = AbstractQuery::initByQueryString($query);
        
        /**
         * INSERT or DELETE query
         */
        if ('updateQuery' == AbstractQuery::getQueryType($query)) {
            
            $firstPart = substr($queryObject->getSubType(), 0, 6);
            
            // DELETE DATA query
            if ('delete' == $firstPart) {
                $statement = $this->getStatement($queryObject);
                return $this->deleteMatchingStatements($statement);
            
            // INSERT DATA or INSERT INTO query
            } elseif ('insert' == $firstPart) {
                return $this->addStatements($this->getStatements($queryObject));
                
            // TODO Support 
            // WITH ... DELETE ... WHERE queries
            // WITH ... DELETE ... INSERT ... WHERE queries
            } else {
                throw new \Exception(
                    'WITH-DELETE-WHERE and WITH-DELETE-INSERT-WHERE queries are not supported yet.'
                );
            }
            
        /**
         * ASK query
         */
        } elseif ('askQuery' == AbstractQuery::getQueryType($query)) {
            $statement = $this->getStatement($queryObject);
            return $this->hasMatchingStatement($statement);
            
        /**
         * SELECT query
         */
        } elseif ('selectQuery' == AbstractQuery::getQueryType($query)) {
            $statement = $this->getStatement($queryObject);
            return $this->getMatchingStatements($statement);
            
        /**
         * Unsupported query
         */
        } else {
            throw new \Exception('Unsupported query was given: '. $query);
        }
    }

    /**
     * Create Statement instance based on a given Query instance.
     * 
     * @param  Query     $queryObject Query object which represents a SPARQL query.
     * @return Statement              Statement object
     * @throws \Exception             If query contains more than one triple pattern.
     * @throws \Exception             If more than one graph was found.
     */
    protected function getStatement(Query $queryObject)
    {
        $queryParts = $queryObject->getQueryParts();
        $triplePattern = $queryParts['triple_pattern'];
        
        if (1 == count($triplePattern)) {
            
            /**
             * Triple
             */
            if (false === isset($queryParts['graphs'])) {
                $subject = $this->createNode($triplePattern[0]['s'], $triplePattern[0]['s_type']);
                $predicate = $this->createNode($triplePattern[0]['p'], $triplePattern[0]['p_type']);
                $object = $this->createNode($triplePattern[0]['o'], $triplePattern[0]['o_type']);
                $graph = null;
                
            /**
             * Quad
             * FYI: enter this case only if there is exactly one graph.
             */
            } elseif (true === isset($queryParts['graphs']) 
                && 1 == count($queryParts['graphs'])) {
                $subject = $this->createNode($triplePattern[0]['s'], $triplePattern[0]['s_type']);
                $predicate = $this->createNode($triplePattern[0]['p'], $triplePattern[0]['p_type']);
                $object = $this->createNode($triplePattern[0]['o'], $triplePattern[0]['o_type']);
                $graph = new NamedNodeImpl($queryParts['graphs'][0]);
            
            /**
             * It is not possible that there is graphs set and empty, because it would be ereased before.
             * So if we enter this case there can only be more than one graph which is a problem, because
             * we have a quad or triple.
             */
            } else {
                throw new \Exception('More than one graph was found.');
            }
            
            return new StatementImpl($subject, $predicate, $object, $graph);
            
        } else {
            throw new \Exception('Query contains more than one triple pattern.');
        }
    }

    /**
     * Create statements from query.
     * 
     * @param  Query             $queryObject Query object which represents a SPARQL query.
     * @return StatementIterator StatementIterator object
     */
    protected function getStatements(Query $queryObject)
    {
        $statements = new ArrayStatementIteratorImpl(array());
        foreach ($queryParts as $st) {
            $statements->append($this->getStatement($st));
        }
        return $statements;
    }

    /**
     * Create a Node from string.
     *
     * @param  string $value value of Node
     * @param  string $type type of Node, can be uri, var or literal
     * @return Node   Returns NamedNode, Variable or Literal
     */
    protected function createNode($value, $type)
    {
        if ('uri' == $type) {
            return new NamedNodeImpl($value);
        } elseif ('var' == $type) {
            return new VariableImpl('?' . $value);
        } elseif ('typed-literal' == $type || 'literal' == $type) {
            return new LiteralImpl($value);
        }
    }
}
