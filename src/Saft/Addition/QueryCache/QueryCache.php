<?php

namespace Saft\Addition\QueryCache;

use Saft\Cache\CacheFactory;
use Saft\Rdf\NamedNode;
use Saft\Rdf\Node;
use Saft\Rdf\Statement;
use Saft\Rdf\StatementIterator;
use Saft\Rdf\StatementIteratorFactory;
use Saft\Store\ChainableStore;
use Saft\Store\Store;
use Saft\Sparql\Query\AbstractQuery;
use Saft\Sparql\Query\Query;
use Saft\Sparql\Query\QueryFactory;

/**
 * This class implements a SPARQL query cache, which was described in the following paper:
 *
 * Martin Michael, Jörg Unbehauen, and Sören Auer.
 * "Improving the performance of semantic web applications with SPARQL query caching."
 * The Semantic Web: Research and Applications.
 * Springer Berlin Heidelberg, 2010.
 * 304-318.
 *
 * Link: http://www.informatik.uni-leipzig.de/~auer/publication/caching.pdf
 *
 * ----------------------------------------------------------------------------------------
 *
 * The implementation here uses a key-value-pair based cache mechanism. The original approach was using a
 * relation database to store and manage query cache related entities.
 */
class QueryCache implements Store, ChainableStore
{
    /**
     * @var Cache
     */
    protected $cache;

    /**
     * @var CacheFactory
     */
    private $cacheFactory;

    /**
     * @var array
     */
    protected $latestQueryCacheContainer = array();

    /**
     * Method log. Its an array which saves entry in the order they were given.
     *
     * @var
     */
    protected $log;

    /**
     * @var QueryFactory
     */
    private $queryFactory;

    /**
     * Used in pattern key's as seperator. Here an example for _:
     * http://localhost/Saft/TestGraph/_http://a_*_*
     *
     * @var string
     */
    protected $separator;

    /**
     * @var StatementIteratorFactory
     */
    private $statementIteratorFactory;

    /**
     * @var Store
     */
    protected $successor;

    /**
     * Constructor
     *
     * @param CacheFactory             $cacheFactory
     * @param QueryFactory             $queryFactory
     * @param StatementIteratorFactory $statementIteratorFactory
     * @param array                    $config Configuration array.
     */
    public function __construct(
        CacheFactory $cacheFactory,
        QueryFactory $queryFactory,
        StatementIteratorFactory $statementIteratorFactory,
        array $config
    ) {
        if (isset($config['cacheConfig']) && is_array($config['cacheConfig'])) {
            $this->cacheFactory = $cacheFactory;
            $this->queryFactory = $queryFactory;
            $this->statementIteratorFactory = $statementIteratorFactory;

            $this->cache = $cacheFactory->createCache($config['cacheConfig']);

            $this->log = array();
            $this->separator = '__.__';

        } else {
            throw new \Exception('No cacheConfig array inside the config given.');
        }
    }

    /**
     * Adds multiple Statements to (default-) graph.
     *
     * @param  StatementIterator|array $statements StatementList instance must contain Statement instances
     *                                             which are 'concret-' and not 'pattern'-statements.
     * @param  Node                    $graph      optional Overrides target graph. If set, all statements will
     *                                             be add to that graph, if available.
     * @param  array                   $options    optional It contains key-value pairs and should provide additional
     *                                             introductions for the store and/or its adapter(s).
     * @return boolean Returns true, if function performed without errors. In case an error occur, an exception
     *                 will be thrown.
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
            $this->invalidateByTriplePattern($statements, $graphUri);

            return $this->successor->addStatements($statements, $graph, $options);

        // dont run command by myself
        } else {
            throw new \Exception('QueryCache does not support adding new statements, only by successor.');
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
     * Builds an array which contains all possible pattern for given S, P and O.
     *
     * @param  string $s        Its * or an URI
     * @param  string $p        Its * or an URI
     * @param  string $o        Its * or an URI
     * @param  string $graphUri Graph URI which belongs to given SPO.
     * @return array
     */
    public function buildPatternListBySPO($s, $p, $o, $graphUri)
    {
        // log it
        $this->addToLog(array(
            'method' => 'buildPatternListBySPO',
            'parameter' => array('s' => $s, 'p' => $p, 'o' => $o, 'graphUri' => $graphUri)
        ));

        $patternList = array(
            // this pattern based on the current statement: graphUri_URI|*_URI|*_URI|*
            $graphUri . $this->separator .'*'. $this->separator .'*'. $this->separator .'*',
        );

        /**
         * Generates pattern whereas only one place is set, e.g.: graphUri_http://a/_*_*
         */
        if ('*' !== $s) {
            $patternList[] = $graphUri . $this->separator . $s . $this->separator .'*'. $this->separator .'*';
        }

        if ('*' !== $p) {
            $patternList[] = $graphUri . $this->separator .'*'. $this->separator . $p . $this->separator .'*';
        }

        if ('*' !== $o) {
            $patternList[] = $graphUri . $this->separator .'*'. $this->separator .'*'. $this->separator . $o;
        }

        /**
         * Generates pattern whereas 2 places are set, e.g.: graphUri_http://a/_http://b/_*
         */
        // s and p
        if ('*' !== $s && '*' !== $p) {
            $patternList[] = $graphUri . $this->separator . $s . $this->separator . $p . $this->separator .'*';
        }

        // s and o
        if ('*' !== $s && '*' !== $o) {
            $patternList[] = $graphUri . $this->separator . $s . $this->separator .'*'. $this->separator . $o;
        }

        // p and o
        if ('*' !== $p && '*' !== $o) {
            $patternList[] = $graphUri . $this->separator .'*'. $this->separator . $p . $this->separator . $o;
        }

        /**
         * If all 3 are not *
         */
        if ('*' !== $s && '*' !== $p && '*' !== $o) {
            $patternList[] = $graphUri . $this->separator . $s . $this->separator . $p . $this->separator . $o;
        }

        return $patternList;
    }

    /**
     * Builds an array which contains all possible pattern to cover a given $statement.
     *
     * @param  Statement $statement
     * @param  string    $graphUri
     * @return array
     */
    public function buildPatternListByStatement(Statement $statement, $graphUri)
    {
        // log it
        $this->addToLog(array(
            'method' => 'buildPatternListByStatement',
            'parameter' => array(
                'statement' => $statement,
                'graphUri' => $graphUri,
            )
        ));

        if (true === $statement->getSubject()->isNamed()) {
            $subject = $statement->getSubject()->getUri();
        } else {
            $subject = '*';
        }

        /**
         * Build pattern part for predicate
         */
        if (true === $statement->getPredicate()->isNamed()) {
            $predicate = $statement->getPredicate()->getUri();
        } else {
            $predicate = '*';
        }

        /**
         * Build pattern part for predicate
         */
        if (true === $statement->getObject()->isNamed()) {
            $object = $statement->getObject()->getUri();
        } else {
            $object = '*';
        }

        return $this->buildPatternListBySPO($subject, $predicate, $object, $graphUri);
    }

    /**
     * Builds an array which contains all possible pattern to cover a given triple pattern.
     *
     * @param  array  $triplePattern
     * @param  string $graphUri
     * @return array
     */
    public function buildPatternListByTriplePattern(array $triplePattern, $graphUri)
    {
        // log it
        $this->addToLog(array(
            'method' => 'buildPatternListByTriplePattern',
            'parameter' => array(
                'triplePattern' => $triplePattern,
                'graphUri' => $graphUri,
            )
        ));

        if ('uri' === $triplePattern['s_type']) {
            $subject = $triplePattern['s'];
        } else {
            $subject = '*';
        }

        if ('uri' === $triplePattern['p_type']) {
            $predicate = $triplePattern['p'];
        } else {
            $predicate = '*';
        }

        if ('uri' === $triplePattern['o_type']) {
            $object = $triplePattern['o'];
        } else {
            $object = '*';
        }

        return $this->buildPatternListBySPO($subject, $predicate, $object, $graphUri);
    }

    /**
     * Removes all statements from a (default-) graph which match with given statement.
     *
     * @param  Statement $statement          It can be either a concrete or pattern-statement.
     * @param  Node      $graph     optional Overrides target graph. If set, all statements will be delete in
     *                                       that graph.
     * @param  array     $options   optional It contains key-value pairs and should provide additional
     *                                       introductions for the store and/or its adapter(s).
     * @return boolean Returns true, if function performed without errors. In case an error occur, an exception
     *                 will be thrown.
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
            $this->invalidateByTriplePattern(
                $this->statementIteratorFactory->createIteratorFromArray(array($statement)),
                $graphUri
            );

            return $this->successor->deleteMatchingStatements($statement, $graph, $options);

        // dont run command by myself
        } else {
            throw new \Exception('QueryCache does not support delete matching statements, only by successor.');
        }
    }

    /**
     * Returns array with graphUri's which are available.
     *
     * @return array      Array which contains graph URI's as values and keys.
     * @throws \Exception If no successor is set but this function was called.
     */
    public function getGraphs()
    {
        // log it
        $this->addToLog(array('method' => 'getGraphs'));

        // if successor is set, ask it first before run the command yourself.
        if ($this->successor instanceof Store) {
            return $this->successor->getGraphs();

        // dont run command by myself
        } else {
            throw new \Exception('QueryCache does not support get available graphs, only by successor.');
        }
    }

    /**
     * Returns active cache instance.
     *
     * @return Cache
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * @return Store Store instance
     */
    public function getChainSuccessor()
    {
        return $this->successor;
    }

    /**
     * Returns latest query cache container. It only contains contains which were created during this active
     * PHP session!
     *
     * @return array
     */
    public function getLatestQueryCacheContainer()
    {
        return $this->latestQueryCacheContainer;
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
     * - statement's subject is either equal to the subject of the same statement of the graph or it is null.
     * - statement's predicate is either equal to the predicate of the same statement of the graph or it is null.
     * - statement's object is either equal to the object of a statement of the graph or it is null.
     *
     * @param  Statement $statement          It can be either a concrete or pattern-statement.
     * @param  Node      $graph     optional Overrides target graph. If set, you will get all matching
     *                                       statements of that graph.
     * @param  array     $options   optional It contains key-value pairs and should provide additional
     *                                       introductions for the store and/or its adapter(s).
     * @return StatementIterator It contains Statement instances  of all matching statements of the given graph.
     * @todo check if graph URI is invalid
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

        $queryCacheContainer = $this->cache->get($query);

        // check, if there is a cache entry for this statement
        if (null !== $queryCacheContainer) {
            $result = $queryCacheContainer['result'];

        // if no cache entry available, run query by successor and save its result in the cache
        } elseif ($this->successor instanceof Store) {
            $result = $this->successor->getMatchingStatements($statement, $graph, $options);

            $this->saveResult($this->queryFactory->createInstanceByQueryString($query), $result);

        // dont run command by myself
        } else {
            throw new \Exception('QueryCache does not support get matching statements, only by successor.');
        }

        return $result;
    }

    /**
     * Returns a prevoiusly stored result, if available.
     *
     * @param string|Query Query representation: either a string or instance of Query
     * @return null|mixed Mixed if cache entry was found, null otherwise.
     * @throws \Exception If parameter $query is neither a string nor an instance of Query.
     */
    public function getResult($query)
    {
        // instance of Query was given
        if ($query instanceof Query) {
            return $this->cache->get((string)$query);

        // string was given
        } elseif (true === is_string($query)) {
            return $this->cache->get($query);

        // invalid $query parameter
        } else {
            throw new \Exception('Parameter $query is neither a string nor an instance of Query.');
        }
    }

    /**
     * Get information about the store and its features.
     *
     * @return array Array which contains information about the store and its features.
     */
    public function getStoreDescription()
    {
        // log it
        $this->addToLog(array('method' => 'getStoreDescription'));

        // if successor is set, ask it first before run the command yourself.
        if ($this->successor instanceof Store) {
            return $this->successor->getStoreDescription();

        // dont run command by myself
        } else {
            throw new \Exception('QueryCache does not support getting a store description, only by successor.');
        }
    }

    /**
     * redirects to the query method.
     * Returns true or false depending on whether or not the statements pattern
     * has any matches in the given graph.
     *
     * @param  Statement $statement          It can be either a concrete or pattern-statement.
     * @param  Node      $graph     optional Overrides target graph.
     * @param  array     $options   optional It contains key-value pairs and should provide additional
     *                                       introductions for the store and/or its adapter(s).
     * @return boolean Returns true if at least one match was found, false otherwise.
     * @todo cache ask queries
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
        $queryCacheContainer = $this->cache->get($query);

        // check, if there is a cache entry for this statement
        if (null !== $queryCacheContainer) {
            return $queryCacheContainer['result'];

        // if successor is set, ask it first before run the command yourself.
        } elseif ($this->successor instanceof Store) {
            $result = $this->successor->hasMatchingStatement($statement, $graph, $options);
            $this->saveResult($this->queryFactory->createInstanceByQueryString($query), $result);
            return $result;

        // dont run command by myself
        } else {
            throw new \Exception('QueryCache does not support has matching statement calls, only by successor.');
        }
    }

    /**
     * Invalidate according cache entries to the given $graphUri. That means, that all query cache entries,
     * which belonging to this graphURI will be invalidated.
     *
     * @param string $graphUri
     */
    public function invalidateByGraphUri($graphUri)
    {
        // log it
        $this->addToLog(array(
            'method' => 'invalidateByGraphUri',
            'parameter' => array(
                'graphUri' => $graphUri
            )
        ));

        $queryList = $this->cache->get($graphUri);

        // if a cache entry for this graph URI was found.
        if (null !== $queryList) {
            foreach ($queryList as $query) {
                $this->invalidateByQuery($this->queryFactory->createInstanceByQueryString($query));
            }
        }
    }

    /**
     * Invalidates according graph Uri entries, the result and all triple pattern.
     *
     * @param Query $queryObject All data according to this query will be invalidated.
     */
    public function invalidateByQuery(Query $queryObject)
    {
        // log it
        $this->addToLog(array(
            'method' => 'invalidateByQuery',
            'parameter' => array(
                'queryObject' => $queryObject
            )
        ));

        $query = $queryObject->getQuery();

        // load query cache container by given query
        $queryCacheContainer = $this->cache->get($query);

        /**
         * remove according query from the query list which belongs to one of the graph URI's in the query
         * cache container.
         */
        if (true === is_array($queryCacheContainer['graph_uris'])) {
            foreach ($queryCacheContainer['graph_uris'] as $graphUri) {
                $queryList = $this->cache->get($graphUri);

                unset($queryList[$query]);

                // if graphUri entry is empty after the operation, remove it from the cache
                if (0 == count($queryList)) {
                    $this->cache->delete($graphUri);

                // otherwise save updated entry
                } else {
                    $this->cache->set($graphUri, $queryList);
                }
            }
        }

        // check for according triple pattern
        if (true === is_array($queryCacheContainer['triple_pattern'])) {
            foreach ($queryCacheContainer['triple_pattern'] as $patternKey) {
                $queryList = $this->cache->get($patternKey);

                unset($queryList[$query]);

                // if patternKey entry is empty after the operation, remove it from the cache
                if (0 == count($queryList)) {
                    $this->cache->delete($patternKey);

                // otherwise save updated entry
                } else {
                    $this->cache->set($patternKey, $queryList);
                }
            }
        }

        /**
         * Remove query cache container
         */
        $this->cache->delete($query);
    }

    /**
     * Invalidate all query cache entries which belong to Statement Iterator entries.
     *
     * @param  StatementIterator|array $statements          Statement iterator containing statements
     *                                                      to be created. They will be invalidated first.
     * @param  string                  $graphUri   optional URI of the graph which is related to the
     *                                                      statements. If null, the graph of the
     *                                                      statement will be used.
     * @throws \Exception If no graph URI for a certain statement is available.
     */
    public function invalidateByTriplePattern($statements, $graphUri = null)
    {
        // log it
        $this->addToLog(array(
            'method' => 'invalidateByTriplePattern',
            'parameter' => array(
                'statements' => $statements,
                'graphUri' => $graphUri
            )
        ));

        $patternList = array();

        foreach ($statements as $statement) {
            /**
             * Find right graph URI.
             */
            // no graph URI given, but statement has one avaiable.
            if (null === $graphUri && null !== $statement->getGraph()) {
                $graphUri = $statement->getGraph()->getUri();

            // no graph URI given and statement has no one as well.
            } elseif (null === $graphUri && null === $statement->getGraph()) {
                throw new \Exception('No graph URI available for statement: ' . $statement);
            }

            /**
             * Build patterns to match all combinations for $statement
             */
            $patternList = array_merge($patternList, $this->buildPatternListByStatement($statement, $graphUri));
        }

        $patternList = array_unique($patternList);

        /**
         * go through query list for each pattern and invalidate according query
         */
        foreach ($patternList as $pattern) {
            $queryList = $this->cache->get($pattern);
            if (null !== $queryList) {
                foreach ($queryList as $query) {
                    $this->invalidateByQuery($this->queryFactory->createInstanceByQueryString($query));
                }
            }
        }
    }

    /**
     * This method sends a SPARQL query to the store.
     *
     * @param  string $query            The SPARQL query to send to the store.
     * @param  array  $options optional It contains key-value pairs and should provide additional
     *                                  introductions for the store and/or its adapter(s).
     * @return \Saft\Sparql\Result Returns result of the query. Depending on the query type, it returns either
     *                            an instance of ResultIterator, StatementIterator, or ResultValue
     * @throws \Exception If query is no string.
     * @throws \Exception If query is malformed.
     * @throws \Exception If $options[resultType] = is neither extended nor array
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

        /**
         * run command by myself and check, if the cache already contains the result to this query.
         */
        $queryCacheContainer = $this->cache->get($query);

        // if a cache entry was found. usually at the beginning, no cache entry is available. so ask the
        // successor and save its result as query result in the cache. the next call of this function will
        // lead to reuse of cache entry.
        if (null !== $queryCacheContainer) {
            $result = $queryCacheContainer['result'];

        // no cache entry was found
        } else {
            // if successor is set, ask it and remember its result
            if ($this->successor instanceof Store) {
                $result = $this->successor->query($query, $options);
                $this->saveResult($this->queryFactory->createInstanceByQueryString($query), $result);

            // if successor is not set, throw exception
            } else {
                throw new \Exception('QueryCache does not support querying, only by successor.');
            }
        }

        return $result;
    }

    /**
     * Saves the result to a given query. This function creates a couple of entry in the cache to interconnect
     * query parts with the result.
     *
     * @param Query $queryObject Query instance which represents the query to the result.
     * @param mixed $result      Represents the result to a given query.
     */
    public function saveResult(Query $queryObject, $result)
    {
        // log it
        $this->addToLog(array(
            'method' => 'saveResult',
            'parameter' => array(
                'queryObject' => $queryObject,
                'result' => $result
            )
        ));

        // invalidate previous result
        $this->invalidateByQuery($queryObject);

        $queryCacheContainer = array('graph_uris' => array(), 'triple_pattern' => array());

        $query = $queryObject->getQuery();
        $queryParts = $queryObject->getQueryParts();

        /**
         * Save reference between all graphs of the given query to the query itself.
         *
         *      graph1 ---> query ---> query container
         *             |
         *      graph2 ´
         *      ...
         */
        if (true === isset($queryParts['graphs'])) {
            foreach ($queryParts['graphs'] as $graphUri) {
                $queryList = $this->cache->get($graphUri);

                if (null === $queryList) {
                    $queryList = array();
                }

                $queryList[$query] = $query;

                $this->cache->set($graphUri, $queryList);

                // save reference to this graph URI in later query cache container
                $queryCacheContainer['graph_uris'][$graphUri] = $graphUri;
            }
        }

        /**
         * Save reference between all triple pattern of the given query to the query itself.
         *
         *      triple pattern ---> query ---> query container
         *                     |
         *      triple pattern ´
         *
         *
         * Assumption here is:         *
         * - for triples: All triples belong to ALL graphs of the query.
         * - for quads: Each triple belongs only to the graph of the quad.
         */
        foreach ($queryParts['triple_pattern'] as $pattern) {
            foreach ($queryParts['graphs'] as $graphUri) {
                /**
                 * generate hashes/placeholders for subject, predicate and object of the triple pattern
                 */
                $subjectHash = 'uri' == $pattern['s_type'] ? $pattern['s'] : '*';
                $predicateHash = 'uri' == $pattern['p_type'] ? $pattern['p'] : '*';
                $objectHash = 'uri' == $pattern['o_type'] ? $pattern['o'] : '*';

                /**
                 * Generate pattern key which contains graphUri, S, P and O. After that try to load existing
                 * query list from cache with generated $patternKey.
                 */
                $patternKey = $graphUri . $this->separator . $subjectHash . $this->separator . $predicateHash .
                    $this->separator . $objectHash;

                $queryList = $this->cache->get($patternKey);

                if (null === $queryList) {
                    $queryList = array();
                }

                $queryList[$query] = $query;

                $this->cache->set($patternKey, $queryList);

                // save reference to this pattern in later query cache container
                $queryCacheContainer['triple_pattern'][$patternKey] = $patternKey;
            }
        }

        /**
         * Create and save container for query cache itself. It contains query, according result and references
         * to upper graph URI's and triple pattern.
         *
         *      query ---> query container
         */
        $queryCacheContainer['result'] = $result;
        $queryCacheContainer['query'] = $query;

        $this->cache->set($query, $queryCacheContainer);

        $this->latestQueryCacheContainer[] = $queryCacheContainer;
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
}
