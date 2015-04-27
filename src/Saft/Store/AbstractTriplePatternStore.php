<?php

namespace Saft\Store;

use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\AbstractNamedNode;
use Saft\Rdf\AnyPatternImpl;
use Saft\Rdf\LiteralImpl;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\NodeFactory;
use Saft\Rdf\Node;
use Saft\Rdf\Statement;
use Saft\Rdf\StatementImpl;
use Saft\Rdf\StatementIterator;
use Saft\Sparql\Query\AbstractQuery;
use Saft\Sparql\Query\Query;

/**
 * Predefined Pattern-statement Store. The Triple-methods need to be implemented in the specific statement-store.
 * The query method is defined in the abstract class and reroute to the triple-methods.
 */
abstract class AbstractTriplePatternStore implements Store
{

    /**
     * Adds multiple Statements to (default-) graph.
     *
     * @param  StatementIterator $statements          StatementList instance must contain Statement instances
     *                                                which are 'concret-' and not 'pattern'-statements.
     * @param  Node              $graph      optional Overrides target graph. If set, all statements will
     *                                                be add to that graph, if available.
     * @param  array             $options    optional It contains key-value pairs and should provide additional
     *                                                introductions for the store and/or its adapter(s).
     * @return array Array containing all given arguments.
     *
     * @todo implement usage of graph inside the statement(s). create groups for each graph
     */
    public function addStatements(StatementIterator $statements, Node $graph = null, array $options = array())
    {
        /**
         * This basic implementation only returns what was given. It is basically for test purposes and
         * later real implementations will rather override this function.
         */
        return array($statements, $graph, $options);
    }

    /**
     * Removes all statements from a (default-) graph which match with given statement.
     *
     * @param  Statement $statement          It can be either a concrete or pattern-statement.
     * @param  Node      $graph     optional Overrides target graph. If set, all statements will be delete in
     *                                       that graph.
     * @param  array     $options   optional It contains key-value pairs and should provide additional
     *                                       introductions for the store and/or its adapter(s).
     * @return array Array containing all given arguments.
     */
    public function deleteMatchingStatements(Statement $statement, Node $graph = null, array $options = array())
    {
        /**
         * This basic implementation only returns what was given. It is basically for test purposes and
         * later real implementations will rather override this function.
         */
        return array($statement, $graph, $options);
    }

    /**
     * It gets all statements of a given graph which match the following conditions:
     * - statement's subject is either equal to the subject of the same statement of the graph or it is null.
     * - statement's predicate is either equal to the predicate of the same statement of the graph or it is null.
     * - statement's object is either equal to the object of a statement of the graph or it is null.
     *
     * @param  Statement $statement          It can be either a concrete or pattern-statement.
     * @param  Node      $graph     optional Overrides target graph. If set, you will get all
     *                                       matching statements of that graph.
     * @param  array     $options   optional It contains key-value pairs and should provide additional
     *                                       introductions for the store and/or its adapter(s).
     * @return array Array containing all given arguments.
     */
    public function getMatchingStatements(Statement $statement, Node $graph = null, array $options = array())
    {
        /**
         * This basic implementation only returns what was given. It is basically for test purposes and
         * later real implementations will rather override this function.
         */
        return array($statement, $graph, $options);
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
        $nodeFactory = new NodeFactory();
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

        if (1 == count($tupleInformation)) {
            /**
             * Triple
             */
            if ('triple' == $tupleType) {
                $subject = $nodeFactory->getInstance($tupleInformation[0]['s'], $tupleInformation[0]['s_type']);
                $predicate = $nodeFactory->getInstance($tupleInformation[0]['p'], $tupleInformation[0]['p_type']);
                $object = $nodeFactory->getInstance($tupleInformation[0]['o'], $tupleInformation[0]['o_type']);
                $graph = null;

            /**
             * Quad
             */
            } elseif ('quad' == $tupleType) {
                $subject = $nodeFactory->getInstance($tupleInformation[0]['s'], $tupleInformation[0]['s_type']);
                $predicate = $nodeFactory->getInstance($tupleInformation[0]['p'], $tupleInformation[0]['p_type']);
                $object = $nodeFactory->getInstance($tupleInformation[0]['o'], $tupleInformation[0]['o_type']);
                $graph = $nodeFactory->getInstance($tupleInformation[0]['g'], 'uri');
            }

            // no else neccessary, because otherwise the upper exception would be thrown if tupleType is neither
            // quad or triple.

            return new StatementImpl($subject, $predicate, $object, $graph);

        } else {
            throw new \Exception('Query contains more than one triple- respectivly quad pattern.');
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
        $queryParts = $queryObject->getQueryParts();
        $nodeFactory = new NodeFactory();

        $statements = new ArrayStatementIteratorImpl(array());

        // if only triples, but no quads
        if (true === isset($queryParts['triple_pattern'])
            && false === isset($queryParts['quad_pattern'])) {
            foreach ($queryParts['triple_pattern'] as $pattern) {
                /**
                 * Create Node instances for S, P and O to build a StatementImpl instance later on
                 */
                $s = $nodeFactory->getInstance($pattern['s'], $pattern['s_type']);
                $p = $nodeFactory->getInstance($pattern['p'], $pattern['p_type']);
                $o = $nodeFactory->getInstance($pattern['o'], $pattern['o_type']);
                $g = null;

                $statements->append(new StatementImpl($s, $p, $o, $g));
            }

        // if only quads, but not triples
        } elseif (false === isset($queryParts['triple_pattern'])
            && true === isset($queryParts['quad_pattern'])) {
            foreach ($queryParts['quad_pattern'] as $pattern) {
                /**
                 * Create Node instances for S, P and O to build a StatementImpl instance later on
                 */
                $s = $nodeFactory->getInstance($pattern['s'], $pattern['s_type']);
                $p = $nodeFactory->getInstance($pattern['p'], $pattern['p_type']);
                $o = $nodeFactory->getInstance($pattern['o'], $pattern['o_type']);
                $g = $nodeFactory->getInstance($pattern['g'], $pattern['g_type']);

                $statements->append(new StatementImpl($s, $p, $o, $g));
            }

        // found quads and triples
        } elseif (true === isset($queryParts['triple_pattern'])
            && true === isset($queryParts['quad_pattern'])) {
            throw new \Exception('Query contains quads and triples. That is not supported yet.');

        // neither quads nor triples
        } else {
            throw new \Exception('Query contains neither quads nor triples.');
        }

        return $statements;
    }

    /**
     * Returns true or false depending on whether or not the statements pattern has any matches in the given
     * graph.
     *
     * @param  Statement $statement          It can be either a concrete or pattern-statement.
     * @param  Node      $graph     optional Overrides target graph.
     * @param  array     $options   optional It contains key-value pairs and should provide additional
     *                                       introductions for the store and/or its adapter(s).
     * @return array Array containing all given arguments.
     */
    public function hasMatchingStatement(Statement $statement, Node $graph = null, array $options = array())
    {
        /**
         * This basic implementation only returns what was given. It is basically for test purposes and
         * later real implementations will rather override this function.
         */
        return array($statement, $graph, $options);
    }

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
}
