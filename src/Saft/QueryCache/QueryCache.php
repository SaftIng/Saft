<?php

namespace Saft\QueryCache;

use Saft\Cache\Cache;
use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\Statement;
use Saft\Rdf\StatementIterator;
use Saft\Store\Store;
use Saft\Sparql\Query\AbstractQuery;
use Saft\Sparql\Query\Query;

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
class QueryCache implements Store
{
    /**
     * @var Cache
     */
    protected $cache;
    
    /**
     * @var array
     */
    protected $latestQueryCacheContainer = array();
    
    /**
     * Constructor
     *
     * @param Cache $cache Initialized cache instance.
     */
    public function __construct(Cache $cache)
    {
        $this->init($cache);
    }
    
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
     */
    public function addStatements(StatementIterator $statements, $graphUri = null, array $options = array())
    {
        // if successor is set, ask it first before run the command yourself.
        if ($this->successor instanceof Store) {
            $this->invalidateBySubjectResources($statements, $graphUri);
            
            return $this->successor->addStatements($statements, $graphUri, $options);
            
        // dont run command by myself
        } else {
            throw new \Exception('QueryCache does not support adding new statements.');
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
        // if successor is set, ask it first before run the command yourself.
        if ($this->successor instanceof Store) {
            $this->invalidateBySubjectResources(new ArrayStatementIteratorImpl(array($statement)), $graphUri);
            
            return $this->successor->deleteMatchingStatements($statement, $graphUri, $options);
            
        // dont run command by myself
        } else {
            throw new \Exception('QueryCache does not support delete matching statements.');
        }
    }
    
    /**
     * Returns array with graphUri's which are available.
     *
     * @return array Array which contains graph URI's as values and keys.
     */
    public function getAvailableGraphs()
    {
        // if successor is set, ask it first before run the command yourself.
        if ($this->successor instanceof Store) {
            return $this->successor->getAvailableGraphs();
            
        // run command by myself
        } else {
            // TODO think about some key-value solution to store available graphs once they got returned by
            //      the successor
            return array();
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
     * It gets all statements of a given graph which match the following conditions:
     * - statement's subject is either equal to the subject of the same statement of the graph or it is null.
     * - statement's predicate is either equal to the predicate of the same statement of the graph or it is null.
     * - statement's object is either equal to the object of a statement of the graph or it is null.
     *
     * @param  Statement $statement          It can be either a concrete or pattern-statement.
     * @param  string    $graphUri  optional Overrides target graph. If set, you will get all matching
     *                                       statements of that graph.
     * @param  array     $options   optional It contains key-value pairs and should provide additional
     *                                       introductions for the store and/or its adapter(s).
     * @return StatementIterator It contains Statement instances  of all matching statements of the given graph.
     * @todo check if graph URI is invalid
     */
    public function getMatchingStatements(Statement $statement, $graphUri = null, array $options = array())
    {
        /**
         * build matching query and check for cache entry
         */
        // Remove, maybe available, graph from given statement and put it into an iterator.
        // reason for the removal of the graph is to avoid quads in the query. Virtuoso wants the graph
        // in the FROM part.
        $query = 'SELECT ?s ?p ?o FROM <'. $graphUri .'> WHERE { ?s ?p ?o ';
        // create shortcuts for S, P and O
        $s = $statement->getSubject();
        $p = $statement->getPredicate();
        $o = $statement->getObject();
            
        // add filter, if subject is a named node or literal
        if (true === $s->isNamed() || true == $s->isLiteral()) {
            $query .= 'FILTER (str(?s) = "'. $s->getUri() .'") ';
        }
        
        // add filter, if predicate is a named node or literal
        if (true === $p->isNamed() || true == $p->isLiteral()) {
            $query .= 'FILTER (str(?p) = "'. $p->getUri() .'") ';
        }
        
        // add filter, if predicate is a named node or literal
        if (true === $o->isNamed() || true == $o->isLiteral()) {
            $query .= 'FILTER (str(?o) = "'. $o->getValue() .'") ';
        }
        
        $query .= '}';
        
        $queryId = $this->generateShortId($query);
        $result = $this->cache->get($queryId);
        
        // check, if there is a cache entry for this statement
        if (null !== $result) {
            $result = $result['result'];
         
        // if no cache entry available, run query by successor and save its result in the cache
        } elseif ($this->successor instanceof Store) {
            $result = $this->successor->getMatchingStatements($statement, $graphUri, $options);
            
            $this->rememberQueryResult($query, $result);
            
        // dont run command by myself
        } else {
            throw new \Exception('QueryCache does not support get matching statements without a successor.');
        }
        
        return $result;
    }
    
    /**
     * Get information about the store and its features.
     *
     * @return array Array which contains information about the store and its features.
     */
    public function getStoreDescription()
    {
        // if successor is set, ask it first before run the command yourself.
        if ($this->successor instanceof Store) {
            return $this->successor->getStoreDescription();
            
        // dont run command by myself
        } else {
            throw new \Exception('QueryCache does not support getting a store description.');
        }
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
     * @todo cache ask queries
     */
    public function hasMatchingStatement(Statement $statement, $graphUri = null, array $options = array())
    {
        // if successor is set, ask it first before run the command yourself.
        if ($this->successor instanceof Store) {
            return $this->successor->hasMatchingStatement($statement, $graphUri, $options);
            
        // dont run command by myself
        } else {
            throw new \Exception('QueryCache does not support has matching statement calls.');
        }
    }
    
    /**
     * Initialize the QueryCache instance.
     *
     * @param Cache $cache
     */
    public function init(Cache $cache)
    {
        $this->cache = $cache;
        
        // Contains a list of cache IDs which each refers to a list of QueryCache entries, which are belonging 
        // together
        $this->relatedQueryCacheEntryList = '';
    }
    
    /**
     * Invalidates according graphId entry, the result and all triple pattern.
     *
     * @param  string  $query                                       All data according to this query will be
     *                                                              invalidated.
     * @param  boolean $checkTransaction optionally                 True, if you wanna check for active transactions.
     *                                                              False if you just want to execute regardless of
     *                                                              active transactions.
     * @param  boolean $checkForRelatedQueryCacheEntries optionally True, if you wanna check, if there are
     *                                                              related QueryCache entries and if so, invalidate
     *                                                              them.
     * @throw \Exception
     */
    public function invalidateByQuery($query, $checkTransaction = true, $checkForRelatedQueryCacheEntries = true)
    {
        // if a transaction is active, stop further execution and save this function
        // call under 'placed operations' of the according active transaction
        if (true === $checkTransaction && true === $this->isATransactionActive()) {
            // save function call + parameter
            $this->addPlacedOperation(
                // function name
                'invalidateByQuery',
                // parameter
                array(
                    'query' => $query,
                    'checkTransaction' => false
                )
            );
            return;
        }
        $queryId = $this->generateShortId($query);
        // get according cache entry
        $queryContainer = $this->cache->get($queryId);
        // remove queryId in each according graphId entry
        if (true === is_array($queryContainer['graphIds'])) {
            foreach ($queryContainer['graphIds'] as $graphId) {
                $graphCacheEntry = $this->cache->get($graphId);
                unset($graphCacheEntry[$queryId]);
                // if graphId entry is empty after the operation, remove it from the cache
                if (0 == count($graphCacheEntry)) {
                    $this->cache->delete($graphId);
                    // otherwise save updated entry
                } else {
                    $this->cache->set($graphId, $graphCacheEntry);
                }
            }
        }
        // check for according triple pattern
        if (true === is_array($queryContainer['triplePattern'])) {
            foreach ($queryContainer['triplePattern'] as $graphId => $triplePattern) {
                foreach ($triplePattern as $patternId) {
                    $this->cache->delete($patternId);
                }
            }
        }
        // if activate and if there are related QueryCache entries, invalidate them
        if (true === $checkForRelatedQueryCacheEntries && '' != $queryContainer['relatedQueryCacheEntries']) {
            // get entries
            $relatedQueryCacheEntries = $this->cache->get($queryContainer['relatedQueryCacheEntries']);
            // invalidate entries by their query
            foreach ($relatedQueryCacheEntries as $queryCacheEntryId) {
                $queryContainer = $this->cache->get($queryCacheEntryId);
                if (true === isset($queryContainer['query'])) {
                    $this->invalidateByQuery($queryContainer['query'], false, false);
                }
            }
        }
        if (true === $this->isATransactionActive()) {
            $this->invalidatedEntriesDuringTransaction[$queryId] = $queryId;
        }
        /**
         * Unset query part of the cache
         */
        $this->cache->delete($queryId);
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
        /**
         * run command by myself and check, if the cache already contains the result to this query.
         */
        $queryResult = $this->cache->get($query);
        
        // if a cache entry was found. usually at the beginning, no cache entry is available. so ask the
        // successor and save its result as query result in the cache. the next call of this function will
        // lead to reuse of cache entry.
        if (null !== $queryResult) {
            $result = $queryResult['result'];
        
        // no cache entry was found
        } else {
            // if successor is set, ask it and remember its result
            if ($this->successor instanceof Store) {
                $result = $this->successor->query($query, $options);
                $this->rememberQueryResult($query, $result);
            
            // if successor is not set, return empty array.
            } else {
                $result = new EmptyResult();
                $this->rememberQueryResult($query, $result);
            }
        }
        
        return $result;
    }
    
    /**
     * 
     * @param Query $queryObject
     * @param mixed $result
     * @return 
     * @throws
     */
    public function saveResult(Query $queryObject, $result)
    {
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
                
                $queryList[] = $query;
                
                $this->cache->set($graphUri, $queryList);
                
                // save reference to this graph URI in later query cache container
                $queryCacheContainer['graph_uris'][] = $graphUri;
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
                $patternKey = $graphUri . '_' . $subjectHash . '_' . $predicateHash . '_' . $objectHash;

                $queryList = $this->cache->get($patternKey);
                
                if (null === $queryList) {
                    $queryList = array();
                }
                
                $queryList[] = $query;
                
                $this->cache->set($patternKey, $queryList);
                
                // save reference to this pattern in later query cache container
                $queryCacheContainer['triple_pattern'][] = $patternKey;
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
}
