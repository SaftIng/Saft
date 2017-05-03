<?php

namespace Saft\Addition\Erfurt\QueryCache;

use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Saft\Rdf\NamedNode;
use Saft\Rdf\Node;
use Saft\Rdf\Statement;
use Saft\Rdf\StatementIterator;
use Saft\Rdf\StatementIteratorFactory;
use Saft\Sparql\Query\AbstractQuery;
use Saft\Sparql\Query\Query;
use Saft\Sparql\Query\QueryFactory;
use Saft\Store\ChainableStore;
use Saft\Store\Store;

/**
 * This class provides access to Erfurt's QueryCache implementation which uses MySQL database to store
 * information.
 */
class QueryCache implements ChainableStore
{
    /**
     * Instance of active Erfurt_App
     */
    protected $erfurtApp;

    /**
     * @var array
     */
    protected $log = array();

    /**
     * Instance of Erfurts QueryCache
     */
    protected $queryCache;

    /**
     * @var QueryFactory
     */
    protected $queryFactory;

    /**
     * @var Store
     */
    protected $successor;

    /**
     *
     */
    public function __construct(QueryFactory $queryFactory, array $configuration)
    {
        // enrich given configuration to provide full cache support
        $configuration = $this->enrichConfiguration($configuration);

        // transform given configuration to Zend_Config instance
        $zendConfig = new \Zend_Config($configuration);

        // init Erfurt app
        $this->erfurtApp = \Erfurt_App::getInstance(false);
        $this->erfurtApp->start($zendConfig);

        // Creates cache tables in the database
        $c = new \Erfurt_Cache_Backend_QueryCache_Database();
        $c->createCacheStructure();

        // save reference to the QueryCache
        $this->queryCache = $this->erfurtApp->getQueryCache();

        $this->queryFactory = $queryFactory;
    }

    /**
     * Adds multiple Statements to (default-) graph.
     *
     * @param StatementIterator|array $statements StatementList instance must contain Statement instances which are
     *                                            'concret-' and not 'pattern'-statements.
     * @param Node                    $graph      Overrides target graph. If set, all statements will be add to that
     *                                            graph, if it is available. (optional)
     * @param array                   $options    Key-value pairs which provide additional introductions for the store
     *                                            and/or its adapter(s). (optional)
     * @api
     * @since 0.1
     */
    public function addStatements($statements, Node $graph = null, array $options = array())
    {
        // TODO migrate code to new interface
        $graphUri = null;
        if ($graph !== null) {
            $graphUri = $graph->getUri();
        }

        // log it
        $this->addToLog(array(
            'method' => 'addStatements',
            'parameter' => array(
                'statements' => $statements,
                'graphUri' => $graphUri,
                'options' => $options
            )
        ));

        // if successor is set, ask it first before run the command yourself.
        if ($this->successor instanceof Store) {
            // log it
            $this->addToLog(array(
                'method' => 'invalidate',
                'parameter' => array(
                    'graphUri' => $graphUri,
                    'statements' => $this->generateErfurtStatementArray($statements)
                )
            ));

            // invalidate statements in the cache
            $this->queryCache->invalidate($graphUri, $this->generateErfurtStatementArray($statements));
            // add statements
            return $this->successor->addStatements($statements, $graph, $options);

        // dont run command by myself
        } else {
            throw new \Exception(
                'Addition\Erfurt\QueryCache does not support adding new statements, only by successor.'
            );
        }
    }

    /**
     * Adds an entry to log.
     *
     * @param array $entry
     */
    protected function addToLog(array $entry)
    {
        $index = count($this->log);
        $this->log[$index] = $entry;
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
        // TODO migrate code to new interface
        $graphUri = null;
        if ($graph !== null) {
            $graphUri = $graph->getUri();
        }

        // log it
        $this->addToLog(array(
            'method' => 'deleteMatchingStatements',
            'parameter' => array(
                'statement' => $statement,
                'graphUri' => $graphUri,
                'options' => $options
            )
        ));

        // if successor is set, ask it first before run the command yourself.
        if ($this->successor instanceof Store) {
            // transform given $statement to a one-element statement list
            $statements = $this->statementIteratorFactory->createStatementIteratorFromArray(array($statement));

            // log it
            $this->addToLog(array(
                'method' => 'invalidate',
                'parameter' => array(
                    'graphUri' => $graphUri,
                    'statements' => $this->generateErfurtStatementArray($statements)
                )
            ));

            // invalidate statements in the cache
            $this->queryCache->invalidate($graphUri, $this->generateErfurtStatementArray($statements));

            // delete matching statements
            return $this->successor->deleteMatchingStatements($statement, $graph, $options);

        // dont run command by myself
        } else {
            throw new \Exception(
                'Addition\Erfurt\QueryCache does not support adding new statements, only by successor.'
            );
        }
    }

    public function enrichConfiguration($configuration)
    {
        // set cache dir path to systems standard temp dir
        $configuration['cache']['backend']['file']['cache_dir'] = sys_get_temp_dir();

        return $configuration;
    }

    /**
     * Generates typical statement array of Erfurt which looks like: $statements[$subject][$predicate] = $object
     */
    protected function generateErfurtStatementArray($statements)
    {
        $statementArray = array();

        foreach ($statements as $statement) {
            $subjectUri = $statement->getSubject()->getUri();
            $predicateUri = $statement->getPredicate()->getUri();

            if ($statement->getObject()->isNamed()) {
                $objectArray = array('value' => $statement->getObject()->getUri(), 'type' => 'uri');
            } else {
                $objectArray = array('value' => $statement->getObject()->getValue(), 'type' => 'literal');
            }

            // set subject, if not available
            if (false == isset($statementArray[$subjectUri])) {
                $statementArray[$subjectUri] = array();
            }

            // set predicate, if not available
            if (false == isset($statementArray[$subjectUri][$predicateUri])) {
                $statementArray[$subjectUri][$predicateUri] = array();
            }

            // set S, P and O
            $statementArray[$subjectUri][$predicateUri] = $objectArray;
        }

        return $statementArray;
    }

    /**
     * Returns log array. It contains information about all operations during this active PHP session.
     *
     * @return array
     */
    public function getLog()
    {
        return $this->log;
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
     * @param Node      $graph     Overrides target graph. If set, you will get all matching statements of
     *                             that graph. (optional)
     * @param array     $options   It contains key-value pairs and should provide additional introductions for
     *                             the store and/or its adapter(s). (optional)
     * @return StatementIterator It contains Statement instances  of all matching statements of the given graph.
     * @api
     * @since 0.1
     */
    public function getMatchingStatements(Statement $statement, Node $graph = null, array $options = array())
    {
        // TODO migrate code to new interface
        $graphUri = null;
        if ($graph !== null) {
            $graphUri = $graph->getUri();
        }

        // log it
        $this->addToLog(array(
            'method' => 'getMatchingStatements',
            'parameter' => array(
                'statement' => $statement,
                'graphUri' => $graphUri,
                'options' => $options
            )
        ));

        /**
         * build matching query and check for cache entry
         */
        // create shortcuts for S, P and O
        $s = $statement->getSubject();
        $p = $statement->getPredicate();
        $o = $statement->getObject();

        $query = '';

        // add filter, if subject is a named node or literal
        if (true === $s->isNamed()) {
            $query .= 'FILTER (str(?s) = "'. $s->getUri() .'") ';
        }

        // add filter, if predicate is a named node or literal
        if (true === $p->isNamed()) {
            $query .= 'FILTER (str(?p) = "'. $p->getUri() .'") ';
        }

        // add filter, if predicate is a named node or literal
        if (true === $o->isNamed()) {
            $query .= 'FILTER (str(?o) = "'. $o->getUri() .'") ';
        }
        if (true === $o->isLiteral()) {
            $query .= 'FILTER (str(?o) = "'. $o->getValue() .'") ';
        }

        $query = 'SELECT ?s ?p ?o FROM <'. $graphUri .'> WHERE { ?s ?p ?o '. $query .'}';
        $result = $this->queryCache->load($query, 'plain');

        // check, if there is a cache entry for this statement
        if ($result === \Erfurt_Cache_Frontend_QueryCache::ERFURT_CACHE_NO_HIT) {
            return $result;

        // if successor is set, ask it first before run the command yourself.
        } elseif ($this->successor instanceof Store) {
            $result = $this->successor->getMatchingStatements($statement, $graph, $options);
            $this->queryCache->save($query, 'plain', $result);
            return $result;

        // dont run command by myself
        } else {
            throw new \Exception(
                'Addition\Erfurt\QueryCache does not support has matching statement calls, only by successor.'
            );
        }
    }

    /**
     * Returns Erfurts Erfurt_Cache_Backend_QueryCache_Database instance which represents the QueryCache.
     */
    public function getQueryCacheInstance()
    {
        return $this->queryCache;
    }

    /**
     * Returns true or false depending on whether or not the statements pattern
     * has any matches in the given graph.
     *
     * @param Statement $statement It can be either a concrete or pattern-statement.
     * @param Node      $graph     Overrides target graph. (optional)
     * @param array     $options   It contains key-value pairs and should provide additional introductions for the
     *                             store and/or its adapter(s). (optional)
     * @return boolean Returns true if at least one match was found, false otherwise.
     * @api
     * @since 0.1
     */
    public function hasMatchingStatement(Statement $statement, Node $graph = null, array $options = array())
    {
        // TODO migrate code to new interface
        $graphUri = null;
        if ($graph !== null) {
            $graphUri = $graph->getUri();
        }

        // log it
        $this->addToLog(array(
            'method' => 'hasMatchingStatement',
            'parameter' => array(
                'statement' => $statement,
                'graphUri' => $graphUri,
                'options' => $options
            )
        ));

        /**
         * build matching query and check for cache entry
         */
        // create shortcuts for S, P and O
        $s = $statement->getSubject();
        $p = $statement->getPredicate();
        $o = $statement->getObject();

        $query = '';

        // add filter, if subject is a named node or literal
        if (true === $s->isNamed()) {
            $query .= 'FILTER (str(?s) = "'. $s->getUri() .'") ';
        }

        // add filter, if predicate is a named node or literal
        if (true === $p->isNamed()) {
            $query .= 'FILTER (str(?p) = "'. $p->getUri() .'") ';
        }

        // add filter, if predicate is a named node or literal
        if (true === $o->isNamed()) {
            $query .= 'FILTER (str(?o) = "'. $o->getUri() .'") ';
        }
        if (true == $o->isLiteral()) {
            $query .= 'FILTER (str(?o) = '. $o->getValue() .') ';
        }

        $query = 'ASK FROM <'. $graphUri .'> { ?s ?p ?o '. $query .'}';
        $result = $this->queryCache->load($query, 'plain');

        // check, if there is a cache entry for this statement
        if ($result === \Erfurt_Cache_Frontend_QueryCache::ERFURT_CACHE_NO_HIT) {
            return $result;

        // if successor is set, ask it first before run the command yourself.
        } elseif ($this->successor instanceof Store) {
            $result = $this->successor->hasMatchingStatement($statement, $graph, $options);
            $this->queryCache->save($query, 'plain', $result);
            return $result;

        // dont run command by myself
        } else {
            throw new \Exception(
                'Addition\Erfurt\QueryCache does not support has matching statement calls, only by successor.'
            );
        }
    }

    /**
     * This method sends a SPARQL query to the store.
     *
     * @param string $query   The SPARQL query to send to the store.
     * @param array  $options It contains key-value pairs and should provide additional introductions for the
     *                        store and/or its adapter(s). (optional)
     * @return Result Returns result of the query. Its type depends on the type of the query.
     * @throws \Exception If query is no string, is malformed or an execution error occured.
     * @api
     * @since 0.1
     */
    public function query($query, array $options = array())
    {
        // log it
        $this->addToLog(array(
            'method' => 'query',
            'parameter' => array(
                'query' => $query,
                'options' => $options
            )
        ));

        $result = $this->queryCache->load($query, 'plain');

        // if nothing was found
        if ($result === \Erfurt_Cache_Frontend_QueryCache::ERFURT_CACHE_NO_HIT) {
            // if successor is set, ask it and remember its result
            if ($this->successor instanceof Store) {
                $result = $this->successor->query($query, $options);
                $this->queryCache->save($query, 'plain', $result);

            // if successor is not set, throw exception
            } else {
                throw new \Exception('Addition\Erfurt\QueryCache does not support querying, only by successor.');
            }
        }

        return $result;
    }

    /**
     * Get information about the store and its features.
     *
     * @return array Array which contains information about the store and its features.
     * @api
     * @since 0.1
     */
    public function getStoreDescription()
    {
        // if successor is set, ask it first before run the command yourself.
        if ($this->successor instanceof Store) {
            return $this->successor->getStoreDescription();

        // dont run command by myself
        } else {
            throw new \Exception(
                'Addition\Erfurt\QueryCache does not support getting a store description, only by successor.'
            );
        }
    }

    /**
     * Returns a list of all available graph URIs of the store. It can also respect access control,
     * to only returned available graphs in the current context. But that depends on the implementation
     * and can differ.
     *
     * @return array Array with the graph URI as key and a NamedNode as value for each graph.
     * @api
     * @since 0.1
     */
    public function getGraphs()
    {
        // if successor is set, ask it first before run the command yourself.
        if ($this->successor instanceof Store) {
            return $this->successor->getGraphs();

        // dont run command by myself
        } else {
            throw new \Exception(
                'Addition\Erfurt\QueryCache does not support get available graphs, only by successor.'
            );
        }
    }

    /**
     * Create a new graph with the URI given as Node, if the chain successor was set. QueryCache itself does not
     * support graph management.
     *
     * @param  NamedNode $graph            Instance of NamedNode containing the URI of the graph to create.
     * @param  array     $options optional It contains key-value pairs and should provide additional introductions
     *                                     for the store and/or its adapter(s).
     * @throws \Exception If given $graph is not a NamedNode.
     * @throws \Exception If the given graph could not be created.
     * @throws \Exception If no chain successor set.
     */
    public function createGraph(NamedNode $graph, array $options = array())
    {
        if ($this->getChainSuccessor() instanceof Store) {
            $this->getChainSuccessor()->createGraph($graph, $options);
        } else {
            throw new \Exception('Can not create graph because no chain successor set.');
        }
    }

    /**
     * Removes the given graph from the store, if the chain successor was set. QueryCache itself does not
     * support graph management.
     *
     * @param  NamedNode $graph            Instance of NamedNode containing the URI of the graph to drop.
     * @param  array     $options optional It contains key-value pairs and should provide additional introductions
     *                                     for the store and/or its adapter(s).
     * @throws \Exception If given $graph is not a NamedNode.
     * @throws \Exception If the given graph could not be droped.
     * @throws \Exception If no chain successor set.
     */
    public function dropGraph(NamedNode $graph, array $options = array())
    {
        // TODO invalidate all entries for this graph
        if ($this->getChainSuccessor() instanceof Store) {
            $this->getChainSuccessor()->dropGraph($graph, $options);
        } else {
            throw new \Exception('Can not drop graph because no chain successor set.');
        }
    }

    /**
     * @return Store Store instance
     */
    public function getChainSuccessor()
    {
        return $this->successor;
    }

    /**
     * Set successor instance. This method is useful, if you wanna build chain of instances which implement
     * Saft\Store\Store. It sets another instance which will be later called, if a statement- or
     * query-related function gets called.
     * E.g. you chain a query cache and a virtuoso instance. In this example all queries will be handled by
     * the query cache first, but if no cache entry was found, the virtuoso instance gets called.
     *
     * @param Store $successor
     */
    public function setChainSuccessor(Store $successor)
    {
        $this->successor = $successor;
    }

    /**
     * Proxy function to end a transaction of Erfurts QueryCache.
     */
    public function endTransaction($uniqueID)
    {
        $this->queryCache->endTransaction($uniqueID);
    }

    /**
     * Proxy function to start a transaction of Erfurts QueryCache.
     */
    public function startTransaction($uniqueID)
    {
        $this->queryCache->startTransaction($uniqueID);
    }
}
