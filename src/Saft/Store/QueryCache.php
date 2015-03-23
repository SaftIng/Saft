<?php
namespace Saft\Store;

use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\Statement;
use Saft\Rdf\StatementIterator;
use Saft\Store\StoreInterface;

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
 * The implementation here uses a key-value-pair based cache mechanism.
 */
class QueryCache implements StoreInterface
{
    /**
     * @var string
     */
    protected $activeTransaction;
    
    /**
     * Key-value pair based cache.
     *
     * @var Saft\Cache
     */
    protected $cache;

    /**
     * This list contains all QueryCache entries, which got invalidated during
     * a running block of a transaction with sub-transactions.
     *
     * @var array
     */
    protected $invalidatedEntriesDuringTransaction;
    
    /**
     * Contains latest results which were stored in the cache.
     *
     * @var array
     */
    protected $latestResults;

    /**
     * List of operations on hold until the according transaction ends. The array
     * has a number-based index, whereas the number belongs to a transaction
     *
     * @var array
     */
    protected $placedOperations;

    /**
     * Contains ID of an array saved as QueryCache entry, which elements represents
     * results of the function rememberQueryResult.
     * function.
     *
     * @var string
     */
    protected $relatedQueryCacheEntryList;

    /**
     * @var array
     */
    protected $runningTransactions;
    
    /**
     * If set, all statement- and query related operations have to be in close collaboration with the
     * successor.
     *
     * @var instance which implements Saft\Store\StoreInterface.
     */
    protected $successor;

    /**
     * Set the mode which determines how to handle transactions. Possible are:
     *
     *  0 = Transactions depending on each other, which means, if one operation of
     *      a transaction gets invalidated or was not successfully executed, all
     *      according transactions and their operations will be invalidated as well.
     *
     *  1 = ??
     *
     * @var int
     */
    protected $transactionMode;

    /**
     * Constructor
     *
     * @param \Saft\Cache $cache Initialized cache instance.
     */
    public function __construct(\Saft\Cache $cache)
    {
        $this->init($cache);
    }

    /**
     * Remembers one more placed operation.
     *
     * @param  string $functionName
     * @param  array  $parameter
     */
    public function addPlacedOperation($functionName, $parameter)
    {
        $this->placedOperations[$this->activeTransaction][] = array(
            'function' => $functionName,
            'parameter' => $parameter
        );
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
        if ($this->successor instanceof StoreInterface) {
            $this->invalidateSubjectResources($statements, $graphUri);
            
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
        if ($this->successor instanceof StoreInterface) {
            $this->invalidateSubjectResources(new ArrayStatementIteratorImpl(array($statement)), $graphUri);
            
            return $this->successor->deleteMatchingStatements($statement, $graphUri, $options);
            
        // dont run command by myself
        } else {
            throw new \Exception('QueryCache does not support delete matching statements.');
        }
    }
    
    /**
     * Drops an existing graph.
     *
     * @param string $graphUri          URI of the graph to drop.
     * @param array  $options  optional It contains key-value pairs and should provide additional introductions
     *                                  for the store and/or its adapter(s).
     * @throw \Exception
     */
    public function dropGraph($graphUri, array $options = array())
    {
        // if successor is set, ask it first before run the command yourself.
        if ($this->successor instanceof StoreInterface) {
            // if successor has this function and it is callable
            if (true === is_callable(array($this->successor, 'dropGraph'), true)) {
                // delete according query cache entries
                $this->invalidateByGraphUri($graphUri);
                
                // call dropGraph on successor
                return $this->successor->dropGraph($graphUri, $options);
            
            // otherwise throw exception
            } else {
                throw new \Exception('Successor does not have a callable dropGraph function.');
            }
            
        // dont run command by myself
        } else {
            throw new \Exception('QueryCache does not support droping graphs.');
        }
    }

    /**
     * Executes an operation which is either invalidateByGraphUri, invalidateByQuery or rememberQueryResult.
     *
     * @param array $operation Array containing information of a function to execute
     * @throw \Exception If key 'function' is not set or invalid.
     */
    public function executeOperation($operation)
    {
        switch ($operation['function']) {
            // invalidate cache entries by graphUri
            case 'invalidateByGraphUri':
                $this->invalidateByGraphUri(
                    $operation['parameter']['graphUri'],
                    $operation['parameter']['checkTransaction']
                );
                break;

            // invalidate cache entries by query
            case 'invalidateByQuery':
                $this->invalidateByQuery(
                    $operation['parameter']['query'],
                    $operation['parameter']['checkTransaction']
                );
                break;

            // save query with according result in cache
            case 'rememberQueryResult':
                $this->rememberQueryResult(
                    $operation['parameter']['query'],
                    $operation['parameter']['result'],
                    $operation['parameter']['checkTransaction']
                );
                break;

            default:
                throw new \Exception('Key "function" is not set or invalid: '. $operation['function']);
                break;
        }
    }

    /**
     * Generates a simple string containing only numbers and letters to be able to use it as cache identifier.
     * Special signs can causing trouble, especially : /
     *
     * @param string $string String to generate a short ID from
     * @return string Generated short ID
     */
    public function generateShortId($string)
    {
        return 'saft-qC-' . substr(hash('sha256', $string), 0, 30);
    }

    /**
     * Returns the ID of the active transaction.
     *
     * @return int ID of the active transaction
     */
    public function getActiveTransaction()
    {
        return $this->activeTransaction;
    }
    
    /**
     * redirects to the query method.
     * Returns array with graphUri's which are available.
     *
     * @return array Array which contains graph URI's as values and keys.
     */
    public function getAvailableGraphs()
    {
        // if successor is set, ask it first before run the command yourself.
        if ($this->successor instanceof StoreInterface) {
            return $this->successor->getAvailableGraphs();
            
        // run command by myself
        } else {
            // TODO think about some key-value solution to store available graphs once they got returned by
            //      the successor
            return array();
        }
    }

    /**
     * Returns in stance of \Cache instance in use.
     *
     * @return \Cache Instance of the \Cache in use
     */
    public function getCache()
    {
        return $this->cache;
    }
    
    /**
     * Returns previously set chain successor.
     *
     * @return StoreInterface
     */
    public function getChainSuccessor()
    {
        return $this->successor;
    }

    /**
     * Returns a list of latest results which were stored in the cache, but it does not ask the cache for all
     * previously stored results.
     *
     * @return array
     */
    public function getLatestResults()
    {
        return $this->latestResults;
    }
    
    /**
     * redirects to the query method.
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
        // build matching query and check for cache entry
        $statementIterator = new ArrayStatementIteratorImpl(array($statement));
        $query = 'SELECT * FROM <'. $graphUri .'> WHERE {'. $this->sparqlFormat($statementIterator) .'}';
        $queryId = $this->generateShortId($query);
        $result = $this->cache->get($queryId);
        
        // check, if there is a cache entry for this statement
        if (null !== $result) {
            $result = $result['result'];
         
        // if no cache entry available, run query by successor and save its result in the cache
        } elseif ($this->successor instanceof StoreInterface) {
            $result = $this->successor->getMatchingStatements($statement, $graphUri, $options);
            $this->rememberQueryResult($query, $result);
            
        // dont run command by myself
        } else {
            throw new \Exception('QueryCache does not support get matching statements without a successor.');
        }
        
        return $result;
    }

    /**
     * Returns all placed operation arrays.
     *
     * @return array
     */
    public function getPlacedOperations()
    {
        return $this->placedOperations;
    }

    /**
     * @return array Array of cache entries which refering to linked QueryCache
     *               entries
     */
    public function getRelatedQueryCacheEntryList()
    {
        return $this->relatedQueryCacheEntryList;
    }

    /**
     * Returns an array which lists all transactions and their status (active, finished).
     *
     * @return array Array of number presenting running transactions
     */
    public function getRunningTransactions()
    {
        return $this->runningTransactions;
    }
    
    /**
     * Get information about the store and its features.
     *
     * @return array Array which contains information about the store and its features.
     */
    public function getStoreDescription()
    {
        // if successor is set, ask it first before run the command yourself.
        if ($this->successor instanceof StoreInterface) {
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
     */
    public function hasMatchingStatement(Statement $statement, $graphUri = null, array $options = array())
    {
        // if successor is set, ask it first before run the command yourself.
        if ($this->successor instanceof StoreInterface) {
            return $this->successor->hasMatchingStatement($statement, $graphUri, $options);
            
        // dont run command by myself
        } else {
            throw new \Exception('QueryCache does not support has matching statement calls.');
        }
    }

    /**
     * Initialize the QueryCache instance.
     *
     * @param Saft\Cache $cache Instance of the \Cache in use
     */
    public function init(\Saft\Cache $cache)
    {
        $this->activeTransaction = null;

        $this->cache = $cache;

        $this->invalidatedEntriesDuringTransaction = array();

        $this->placedOperations = array();

        $this->runningTransactions = array();

        // Contains a list of cache IDs which each refers to a list of QueryCache
        // entries, which are belonging together
        $this->relatedQueryCacheEntryList = '';

        // TODO make it configurable
        $this->transactionMode = 0;
    }

    /**
     * Invalidate according cache entries to the given $graphUri. That means, that all queries, which are
     * according to this graph will be invalidated.
     *
     * @param string  $graphUri
     * @param boolean $checkTransaction optional True, if you wanna check for active transactions. False, if
     *                                           you just want to execute regardless of active transactions.
     */
    public function invalidateByGraphUri($graphUri, $checkTransaction = true)
    {
        // if a transaction is active, stop further execution and save this function
        // call under 'placed operations' of the according active transaction
        if (true === $checkTransaction && true === $this->isATransactionActive()) {
            // save function call + parameter
            $this->addPlacedOperation(
                // function name
                'invalidateByGraphUri',
                // parameter
                array(
                    'graphUri' => $graphUri,
                    'checkTransaction' => false
                )
            );

            return;
        }

        $graphId = $this->generateShortId($graphUri);

        // get according query ids
        $queryIds = $this->cache->get($graphId);

        // check if something is there to delete
        if (null !== $queryIds) {
            // get content according to the queryId
            foreach ($queryIds as $queryId) {
                // get query container
                $queryContainer = $this->cache->get($queryId);

                // in case that in the same SPARQL query more than one graph was used,
                // the triple pattern array is set for only the first graph, but empty
                // for all the others, because we unset it in the end of the function
                if (true === is_array($queryContainer['triplePattern'])) {
                    foreach ($queryContainer['triplePattern'] as $triplePattern) {
                        foreach ($triplePattern as $patternId) {
                            $this->cache->delete($patternId);
                        }
                    }
                }

                // if there are related QueryCache entries, invalidate them
                if ('' != $queryContainer['relatedQueryCacheEntries']) {
                    $relatedQueryCacheEntries = $this->cache->get(
                        $queryContainer['relatedQueryCacheEntries']
                    );

                    foreach ($relatedQueryCacheEntries as $queryCacheEntryId) {
                        $queryContainer = $this->cache->get($queryCacheEntryId);

                        if (true === isset($queryContainer['query'])) {
                            $this->invalidateByQuery($queryContainer['query']);
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
        }

        /**
         * Unset graph part of the cache
         */
        $this->cache->delete($graphId);
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
        if (true === $checkForRelatedQueryCacheEntries
            && '' != $queryContainer['relatedQueryCacheEntries']
        ) {
            // get entries
            $relatedQueryCacheEntries = $this->cache->get(
                $queryContainer['relatedQueryCacheEntries']
            );

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
     * Invalidate all query cache entries which refering to given resources (subject).
     *
     * @param StatementIterator $statements Statement iterator containing statements to be created. They will
     *                                      be invalidated first.
     * @param string            $graphUri   URI of the graph which is related to the statements.
     * @throw \Exception
     */
    public function invalidateSubjectResources(StatementIterator $statements, $graphUri)
    {
        $subjectUris = array();
        
        $graphId = $this->generateShortId($graphUri);
        
        // collect all relevant subject URIs
        foreach ($statements as $statement) {
            // check if subject URI was invalidate before, to prevent obsolete work
            if (false === isset($subjectUris[(string)$statement->getSubject()])) {
                // invalidate resource (triple subject)
                $this->invalidateByQuery(
                    'SELECT ?p ?o FROM <'. $graphUri .'> WHERE {'. $statement->getSubject()->toNQuads() .' ?p ?o.}'
                );
                
                // remember triple subject
                $subjectUris[(string)$statement->getSubject()] = true;
            }
        }
        
        // get according query ids
        $queryIds = $this->cache->get($graphId);
        
        // check if something is there to delete
        if (null !== $queryIds) {
            // get content according to the queryId
            foreach ($queryIds as $queryId) {
                // get query container
                $queryContainer = $this->cache->get($queryId);
                
                foreach ($queryContainer['triplePattern'][$graphId] as $pattern) {
                    foreach ($subjectUris as $subjectUri) {
                        $subjectUriId = $this->generateShortId($subjectUri, false);
                        
                        // look for the hashed subject URI of a joker sign on the subjects position ...
                        if (false !== strpos($pattern, $graphId .'_'. $subjectUriId)
                            || false !== strpos($pattern, $graphId .'_*_')) {
                            // ... in case a match takes place, remove everything, which is related to the
                            // according query of the current pattern
                            $this->invalidateByQuery($queryContainer['query']);
                        }
                    }
                }
            }
        }
    }

    /**
     * Checks if a transaction is active.
     *
     * @return boolean True, if at least one transaction is active
     */
    public function isATransactionActive()
    {
        return null !== $this->activeTransaction;
    }
    
    /**
     * This method sends a SPARQL query to the store.
     *
     * @param  string $query            The SPARQL query to send to the store.
     * @param  array  $options optional It contains key-value pairs and should provide additional
     *                                  introductions for the store and/or its adapter(s).
     * @return Saft\Sparql\Result Returns result of the query. Depending on the query type, it returns either
     *                            an instance of ResultIterator, StatementIterator, or ResultValue
     * @throws \Exception If query is no string.
     *                    If query is malformed.
     */
    public function query($query, array $options = array())
    {
        /**
         * run command by myself and check, if the cache already contains the result to this query.
         */
        $queryId = $this->generateShortId($query);
        $queryResult = $this->cache->get($queryId);
        
        // if a cache entry was found. usually at the beginning, no cache entry is available. so ask the
        // successor and save its result as query result in the cache. the next call of this function will
        // lead to reuse of cache entry.
        if (null !== $queryResult) {
            $result = $queryResult['result'];
        
        // no cache entry was found
        } else {
            // if successor is set, ask it and remember its result
            if ($this->successor instanceof StoreInterface) {
                $result = $this->successor->query($query, $options);
                $this->rememberQueryResult($query, $result);
            
            // if successor is not set, return empty array.
            } else {
                $result = array();
                $this->rememberQueryResult($query, $result);
            }
        }
        
        return $result;
    }

    /**
     * Stores the query, result and all associated meta data in the cache to use
     * it later on instead of query the database again.
     *
     * @param  string  $query            SPARQL query
     * @param  array   $result           Result array of previously executed query
     * @param  boolean $checkTransaction optional True, if you wanna check for active transactions. False if
     *                                            you just want to execute regardless of active transactions.
     */
    public function rememberQueryResult($query, $result, $checkTransaction = true)
    {
        // if a transaction is active, stop further execution and save this function
        // call under 'placed operations' of the according active transaction
        if (true === $checkTransaction && true === $this->isATransactionActive()) {
            // save function call + parameter
            $this->addPlacedOperation(
                // function name
                'rememberQueryResult',
                // parameter
                array(
                    'query' => $query,
                    'result' => $result,
                    'checkTransaction' => false
                )
            );

            return;
        }

        /**
         * init query
         */
        $simpleQuery = new \Saft\Sparql\Query($query);
        $queryId = $this->generateShortId($query);


        /**
         * Initialize query container, which later on, stores references to:
         *      + hash ids of the involved graph ids (based on their URIs)
         *      + hash ids of the according triple pattern
         *      + according result which belongs to the query
         */
        $queryCacheEntry = $this->cache->get($queryId);
        if (false !== $queryCacheEntry) {
            // check, if a cache entry with the $queryId already exists; thats
            // usually not possible, but in case it happens, invalidate all cache
            // entries using given $query
            $this->invalidateByQuery($query);
        }

        $queryCacheEntry = array(
            // this field will be handled by transaction related functions
            // its value is an unique ID to a QueryCache entry which contains
            // a list of according QueryCache entries
            'relatedQueryCacheEntries' => '',
            'graphIds' => array()
        );

        /**
         * Initialize graph container, which later on, stores references to the
         * hash id of the given SPARQL query.
         */
        $graphIds = array();
        foreach ($simpleQuery->getFrom() as $graphUri) {
            // generate short hash based on the URI of the graph
            $graphId = $this->generateShortId($graphUri);

            $graphIds[] = $graphId;

            // get cache entry
            $graphContainer = $this->cache->get($graphId);
            if (false === $graphContainer) {
                $graphContainer = array();
            }

            // no doublings possible, because in case the cache entry already exists
            // than it will be used and no further execution of this function takes
            // place
            $graphContainer[$queryId] = $queryId;

            // save updated/created graph entry
            $this->cache->set($graphId, $graphContainer);

            /**
             * save references of the graph ids in the query cache entry, which
             * represents the given SPARQL query. now there is a bidirectional
             * relation between the graphs used in the Query and the Query cache
             * entry itself.
             */
            $queryCacheEntry['graphIds'][] = $graphId;
        }


        /**
         * Triple pattern: Each SPARQL query contains at least one triple pattern
         * in the WHERE clause. We collect all these and save them in the cache,
         * for later use. Is there later on, an add- or drop triple call, or
         * something like this, we will check the related cached triple pattern,
         * if available. In case we found a match, all according data will be deleted
         * from the cache to avoid having outdated data in the cache.
         */
        $triplePatterns = $simpleQuery->getTriplePatterns();
        $hashedTriplePattern = array();

        foreach ($graphIds as $graphId) {
            $hashedTriplePattern[$graphId] = array();

            foreach ($triplePatterns as $triplePattern) {
                // generate hashes/placeholders for subject, predicate and object of
                // the triple pattern
                $subjectHash    = 'uri' == $triplePattern['s_type']
                                  ? $this->generateShortId($triplePattern['s'])
                                  : '*';

                $predicateHash  = 'uri' == $triplePattern['p_type']
                                  ? $this->generateShortId($triplePattern['p'])
                                  : '*';

                $objectHash     = 'uri' == $triplePattern['o_type']
                                  ? $this->generateShortId($triplePattern['o'])
                                  : '*';

                /**
                 * generate pattern key and create a relation between the pattern
                 * itself and the query id
                 */
                $patternKey = $graphId . '_' . $subjectHash . '_' . $predicateHash .
                                         '_' . $objectHash;
                $this->cache->set($patternKey, $queryId);

                // collect all pattern which are related to a certain graph
                $hashedTriplePattern[$graphId][] = $patternKey;
            }
        }

        /**
         * Final update of query cache entry and storing it afterwards.
         */
        $queryCacheEntry['result']          = $result;
        $queryCacheEntry['query']           = $query;
        $queryCacheEntry['triplePattern']   = $hashedTriplePattern;
        $this->cache->set($queryId, $queryCacheEntry);
        
        // remember this result in this instance too.
        $this->latestResults[$queryId] = $queryCacheEntry;
    }
    
    /**
     * Set successor instance. This method is useful, if you wanna build chain of instances which implement
     * Saft\Store\StoreInterface. It sets another instance which will be later called, if a statement- or
     * query-related function gets called.
     * E.g. you chain a query cache and a virtuoso instance. In this example all queries will be handled by
     * the query cache first, but if no cache entry was found, the virtuoso instance gets called.
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
    public function sparqlFormat(StatementIterator $statements, $graphUri = null)
    {
        $query = '';
        foreach ($statements as $st) {
            if ($st instanceof Statement) {
                $con = $st->toSparqlFormat();

                $graph = $st->getGraph();
                //TODO if graphUri is a valid URI
                if (null !== $graphUri) {
                    $con = 'Graph <'. $graphUri .'> {'. $con .'}';
                } elseif (null !== $graph) {
                    $con = 'Graph <'. $graph->__toString() .'> {'. $con .'}';
                }

                $query .= $con .' ';
            } else {
                throw new \Exception('Not a Statement instance');
            }
        }
        return $query;
    }

    /**
     * Starts a new transaction. Means, that all query cache related operations
     * will be redirect to this new transaction.
     *
     * @return void
     */
    public function startTransaction()
    {
        // set new current transaction active (starts with 0)
        $this->activeTransaction = count($this->runningTransactions);

        $this->runningTransactions[$this->activeTransaction] = 'active';

        $this->placedOperations[$this->activeTransaction] = array();
    }

    /**
     * Stops the active transaction. This function assumes this transaction has
     * no nested transactions, which means, that all of its placed operations
     * will be immediately executed.
     *
     * @throws \Exception Various reasons possible
     * @todo Implement rollback support, in case something went wrong
     */
    public function stopTransaction()
    {
        // operations to be done for this transaction
        $placedOperations = $this->placedOperations[$this->activeTransaction];

        // execute placed operations which are belongs to this, currently active,
        // transaction
        foreach ($placedOperations as $operation) {
            $this->executeOperation($operation);
        }

        // if the last active transaction was closed
        if (0 == $this->transactionMode) {
            $relatedQueryCacheEntries = array();

            // go through all placed operations of each transaction and collect
            // all ids of related QueryCache entries
            foreach ($this->placedOperations as $transactionId => $operations) {
                foreach ($operations as $operation) {
                    if ('rememberQueryResult' == $operation['function']) {
                        $queryId = $this->generateShortId($operation['parameter']['query']);
                        $relatedQueryCacheEntries[$queryId] = $queryId;
                    }
                }
            }

            // now we know, which transaction has a relation to which QueryCache
            // entries. We save this information is each of these QueryCache
            // entries, because in case one of these gets invalidated, it will
            // invalidate all according QueryCache entries as well.

            // FYI:
            // this list grows or keeps its size, but it does not shrink

            $entryId = $this->generateShortId(json_encode($relatedQueryCacheEntries));
            $this->cache->set($entryId, $relatedQueryCacheEntries);
            $this->relatedQueryCacheEntryList = $entryId;

            foreach ($relatedQueryCacheEntries as $queryCacheEntryId) {
                if (true === isset($this->invalidatedEntriesDuringTransaction[$queryCacheEntryId])) {
                    continue;
                }

                // load according query cache entry
                $queryContainer = $this->cache->get($queryCacheEntryId);

                // in case there are already query entry IDs, don't override
                // them, but add the new ones
                $queryContainer['relatedQueryCacheEntries'] = $entryId;

                // TODO test the case that there is already according QueryCache
                //      entries

                $this->cache->set($queryCacheEntryId, $queryContainer);
            }

            /**
             * clean up and remove data
             */
            $this->runningTransactions[$this->activeTransaction] = 'finished';

            if (0 < $this->activeTransaction) {
                // set transaction active, which has the highest number but is still
                // active e.g. if current one was 3, then the next active one is 2.
                $transactionIds = array_keys($this->runningTransactions);
                rsort($transactionIds);
                foreach ($transactionIds as $id) {
                    if ('active' == $this->runningTransactions[$id]) {
                        $this->activeTransaction = $id;
                        break;
                    }
                }

                // if the last running transaction gets closed
            } else {
                $this->activeTransaction = null;
                $this->invalidatedEntriesDuringTransaction = array();
                $this->placedOperations = array();
                $this->runningTransactions = array();
            }
        }
    }
}
