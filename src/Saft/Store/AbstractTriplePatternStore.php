<?php

namespace Saft\Store;

use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\NodeFactory;
use Saft\Rdf\Statement;
use Saft\Rdf\StatementFactory;
use Saft\Rdf\StatementIterator;
use Saft\Sparql\Query\AbstractQuery;
use Saft\Sparql\Query\Query;

/**
 * Predefined Pattern-statement Store. The Triple-methods need to be implemented in the specific statement-store.
 * The query method is defined in the abstract class and reroute to the triple-methods.
 */
abstract class AbstractTriplePatternStore implements Store
{
    private $nodeFactory;
    private $statementFactory;

    public function __construct(NodeFactory $nodeFactory, StatementFactory $statementFactory)
    {
        $this->nodeFactory = $nodeFactory;
        $this->statementFactory = $statementFactory;
    }

    /**
     * @param  string     $query            SPARQL query string.
     * @param  string     $options optional Further configuration options.
     * @throws \Exception If unsupported query was given or if WITH-DELETE-WHERE and WITH-DELETE-INSERT-WHERE query was
     *                    given.
     */
    public function query($query, array $options = array())
    {
        $queryObject = AbstractQuery::initByQueryString($query);

        if ('updateQuery' == AbstractQuery::getQueryType($query)) {
            /*
             * INSERT or DELETE query
             */
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
                    'Not yet implemented: WITH-DELETE-WHERE and WITH-DELETE-INSERT-WHERE queries are not supported yet.'
                );
            }
        } elseif ('askQuery' == AbstractQuery::getQueryType($query)) {
            /*
             * ASK query
             */
            $statement = $this->getStatement($queryObject);
            return $this->hasMatchingStatement($statement);
        } elseif ('selectQuery' == AbstractQuery::getQueryType($query)) {
            /*
             * SELECT query
             */
            $statement = $this->getStatement($queryObject);
            return $this->getMatchingStatements($statement);
        } else {
            /*
             * Unsupported query
             */
            throw new \Exception('Unsupported query was given: '. $query);
        }
    }

    /**
     * Create Statement instance based on a given Query instance.
     *
     * @param  Query     $queryObject Query object which represents a SPARQL query.
     * @return Statement Statement object
     * @throws \Exception             If query contains more than one triple pattern.
     * @throws \Exception             If more than one graph was found.
     */
    protected function getStatement(Query $queryObject)
    {
        $queryParts = $queryObject->getQueryParts();

        $tupleInformaton = null;
        $tupleType = null;

        /**
         * Use triple pattern
         */
        if (true === isset($queryParts['triple_pattern'])) {
            $tupleInformation = $queryParts['triple_pattern'];
            $tupleType = 'triple';

        /**
         * Use quad pattern
         */
        } elseif (true === isset($queryParts['quad_pattern'])) {
            $tupleInformation = $queryParts['quad_pattern'];
            $tupleType = 'quad';

        /**
         * Neither triple nor quad information
         */
        } else {
            throw new \Exception(
                'Neither triple nor quad information available in given query object: ' . $queryObject->getQuery()
            );
        }

        if (1 > count($tupleInformation)) {
            throw new \Exception('Query contains more than one triple- respectivly quad pattern.');
        }

        /**
         * Triple
         */
        if ('triple' == $tupleType) {
            $subject = $this->createNodeByValueAndType($tupleInformation[0]['s'], $tupleInformation[0]['s_type']);
            $predicate = $this->createNodeByValueAndType($tupleInformation[0]['p'], $tupleInformation[0]['p_type']);
            $object = $this->createNodeByValueAndType($tupleInformation[0]['o'], $tupleInformation[0]['o_type']);
            $graph = null;

        /**
         * Quad
         */
        } elseif ('quad' == $tupleType) {
            $subject = $this->createNodeByValueAndType($tupleInformation[0]['s'], $tupleInformation[0]['s_type']);
            $predicate = $this->createNodeByValueAndType($tupleInformation[0]['p'], $tupleInformation[0]['p_type']);
            $object = $this->createNodeByValueAndType($tupleInformation[0]['o'], $tupleInformation[0]['o_type']);
            $graph = $this->createNodeByValueAndType($tupleInformation[0]['g'], 'uri');
        }

        // no else neccessary, because otherwise the upper exception would be thrown if tupleType is neither
        // quad or triple.

        return $this->statementFactory->createStatement($subject, $predicate, $object, $graph);
    }

    /**
     * Create statements from query.
     *
     * @param  Query             $queryObject Query object which represents a SPARQL query.
     * @return StatementIterator StatementIterator object
     */
    protected function getStatements(Query $queryObject)
    {
        $queryParts = $queryObject->getQueryParts();

        $statementArray = array();

        // if only triples, but no quads
        if (true === isset($queryParts['triple_pattern'])
            && false === isset($queryParts['quad_pattern'])) {
            foreach ($queryParts['triple_pattern'] as $pattern) {
                /**
                 * Create Node instances for S, P and O to build a Statement instance later on
                 */
                $s = $this->createNodeByValueAndType($pattern['s'], $pattern['s_type']);
                $p = $this->createNodeByValueAndType($pattern['p'], $pattern['p_type']);
                $o = $this->createNodeByValueAndType($pattern['o'], $pattern['o_type']);
                $g = null;

                $statementsArray[] = $this->statementFactory->createStatement($s, $p, $o, $g);
            }

        // if only quads, but not triples
        } elseif (false === isset($queryParts['triple_pattern'])
            && true === isset($queryParts['quad_pattern'])) {
            foreach ($queryParts['quad_pattern'] as $pattern) {
                /**
                 * Create Node instances for S, P and O to build a Statement instance later on
                 */
                $s = $this->createNodeByValueAndType($pattern['s'], $pattern['s_type']);
                $p = $this->createNodeByValueAndType($pattern['p'], $pattern['p_type']);
                $o = $this->createNodeByValueAndType($pattern['o'], $pattern['o_type']);
                $g = $this->createNodeByValueAndType($pattern['g'], $pattern['g_type']);

                $statementsArray[] = $this->statementFactory->createStatement($s, $p, $o, $g);
            }

        // found quads and triples
        } elseif (true === isset($queryParts['triple_pattern'])
            && true === isset($queryParts['quad_pattern'])) {
            throw new \Exception('Query contains quads and triples. That is not supported yet.');

        // neither quads nor triples
        } else {
            throw new \Exception('Query contains neither quads nor triples.');
        }

        return new ArrayStatementIteratorImpl($statementArray);
    }

    protected function createNodeByValueAndType($value, $type)
    {
        /**
         * URI
         */
        if ('uri' == $type) {
            return $this->nodeFactory->createNamedNode($value);

        /**
         * Any Pattern
         */
        } elseif ('var' == $type) {
            return $this->nodeFactory->createAnyPattern();

        /**
         * Typed Literal or Literal
         */
        } elseif ('typed-literal' == $type || 'literal' == $type) {
            return $this->nodeFactory->createLiteral($value);

        /**
         * Unknown type
         */
        } else {
            throw new \Exception('Unknown type given: '. $type);
        }
    }
}
