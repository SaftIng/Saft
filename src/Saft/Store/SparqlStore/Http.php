<?php

namespace Saft\Store\SparqlStore;

use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\Statement;
use Saft\Rdf\StatementImpl;
use Saft\Rdf\StatementIterator;
use Saft\Sparql\Query;
use Saft\Store\StoreInterface;
use Saft\Store\AbstractSparqlStore;

/**
 * SparqlStore implementation of a client which handles store operations via HTTP. It is able to determine some
 * server types by checking response header.
 */
class Http extends AbstractSparqlStore
{
    /**
     * Adapter option array
     *
     * @var array
     */
    protected $adapterOptions = null;

    /**
     * @var \Saft\Net\Client
     */
    protected $client = null;
    
    /**
     * Name of the store, which runs on the server, e.g. virtuoso.
     * 
     * @var string
     */
    protected $storeName = '';

    /**
     * Constructor.
     *
     * @param array $adapterOptions Array containing database credentials
     */
    public function __construct(array $adapterOptions)
    {
        $this->adapterOptions = $adapterOptions;
        
        $this->checkRequirements();

        // Open connection
        $this->openConnection();
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        $this->closeConnection();
    }

    /**
     * Add a new empty and named graph.
     *
     * @param  string $graphUri URI of the graph to create
     * @throws \Exception
     */
    public function addGraph($graphUri)
    {
        $this->client->sendSparqlUpdateQuery('CREATE SILENT GRAPH <'. $graphUri .'>');
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
     * @todo implement usage of graph inside the statement(s). create groups for each graph
     */
    public function addStatements(StatementIterator $statements, $graphUri = null, array $options = array())
    {
        if ('virtuoso' == $this->storeName) {
            /* 
             * Bcause Virtuoso wont accepts queries like:
             *              
             *          INSERT DATA {Graph <...> {<...> <...> <...>}}
             *
             * so we have to change it to:
             *          
             *          INSERT INTO GRAPH <...> {<...> <...> <...>.}
             */
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
             * Create batches out of given statements to improve statement throughput.
             */
            $counter = 0;
            $batchSize = 100;
            $batchStatements = array();

            foreach($statements as $statement) {
                
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
                
                /**
                 * Notice: add a triple to the batch, even a quad was given, because we dont want the quad
                 *         sparqlFormat call.
                 */
                $batchStatements[$graphUriToUse]->append(new StatementImpl(
                    $statement->getSubject(), $statement->getPredicate(), $statement->getObject()
                ));
                
                // after batch is full, execute collected statements all at once
                if (0 === $counter % $batchSize) {
                    
                    /**
                     * $batchStatements is an array with graphUri('s) as key(s) and ArrayStatementIteratorImpl
                     * instances as value. Each entry is related to a certain graph and contains a bunch of 
                     * statement instances.
                     */
                    foreach ($batchStatements as $graphUriToUse => $statementBatch) {
                        $this->query(
                            'INSERT INTO GRAPH <'. $graphUriToUse .'> {'. $this->sparqlFormat($statementBatch) .'}', 
                            $options
                        );
                    }
                    
                    // re-init variables
                    $batchStatements = array();
                }
            }
            
        } else {
            return parent::addStatements($statements, $graphUri, $options);
        }
    }

    /**
     * Checks that all requirements for queries via HTTP are fullfilled.
     * 
     * @return boolean True, if all requirements are fullfilled.
     * @throws \Exception If PHP CURL extension was not loaded.
     */
    public function checkRequirements()
    {
        // check for odbc extension
        if (false === extension_loaded('curl')) {
            throw new \Exception('Http store requires the PHP ODBC extension to be loaded.');
        }

        return true;
    }
    
    /**
     * Deletes all triples of a graph.
     *
     * @param string $graphUri URI of the graph to clear.
     * @throw TODO Exceptions
     */
    public function clearGraph($graphUri)
    {
        $this->query('CLEAR GRAPH <' . $graphUri . '>');
    }

    /**
     * Closes a current connection.
     */
    public function closeConnection()
    {
        $this->client->getClient()->close();
    }
    
    /**
     * Removes all statements from a (default-) graph which match with given statement.
     *
     * @param  Statement $statement          It can be either a concrete or pattern-statement.
     * @param  string    $graphUri  optional Overrides target graph. If set, all statements will be delete in
     *                                       that graph.
     * @param  array     $options   optional It contains key-value pairs and should provide additional
     *                                       introductions for the store and/or its adapter(s).
     * @return boolean Returns true, if function performed without errors. In case
     *                 an error occur, an exception will be thrown.
     */
    public function deleteMatchingStatements(Statement $statement, $graphUri = null, array $options = array())
    {
        /**
         * To be compatible with Virtuoso 6.1.8+, adapt DELETE DATA query. Virtuoso does not understand
         * DELETE DATA calls containing variables such as:
         *
         *      DELETE DATA {
         *          Graph <http://localhost/Saft/TestGraph/> {<http://s/> <http://p/> ?o.}
         *      }
         *
         * So we have to override this method to make it look like:
         *
         *      WITH <http://localhost/Saft/TestGraph/>
         *      DELETE { <http://s/> <http://p/> ?o. }
         *      WHERE { <http://s/> <http://p/> ?o. }
         */
        $statementIterator = new ArrayStatementIteratorImpl(array($statement));

        if (null === $graphUri) {
            $graphUri = $statement->getGraph();
        }

        // if given graphUri and $statements graph are both null, throw exception
        if (null === $graphUri) {
            throw new \Exception('Neither $graphUri nor $statement graph were set.');
        }

        $condition = $this->sparqlFormat($statementIterator);
        $query = 'WITH <'. $graphUri .'> DELETE {'. $condition .'} WHERE {'. $condition .'}';
        $this->query($query, $options);

        return true;
    }

    /**
     * Determines store on the server.
     * 
     * @return string Name of the store on the server. If not possible, returns null.
     */
    public function determineStoreOnServer($responseHeaders)
    {
        $store = null;
        
        // Virtuoso usually set Server key in the response array with value such as: 
        //
        //      Virtuoso/06.01.3127 (Linux) i686-pc-linux-gnu
        if ('Virtuoso' === substr($responseHeaders['Server'], 0, 8)) {
            $store = 'virtuoso';
        }
        
        return $store;
    }

    /**
     * Drops a graph.
     *
     * @param  string $graphUri URI of the graph to remove
     * @throws \Exception
     */
    public function dropGraph($graphUri)
    {
        $this->client->sendSparqlUpdateQuery('DROP SILENT GRAPH <'. $graphUri .'>');
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
     * Returns the URI of which all the queries where send to.
     *
     * @return string URI on which all queries where send to.
     */
    public function getDsn()
    {
        return $this->client->getUri();
    }
    
    /**
     * @return array Empty
     * @todo implement getStoreDescription
     */
    public function getStoreDescription()
    {
        return array();
    }
    
    /**
     * Counts the number of triples in a graph.
     *
     * @param  string $graphUri URI of the graph you wanna count triples
     * @return integer Number of found triples
     * @throw  \Exception If parameter $graphUri is not valid or empty.
     */
    public function getTripleCount($graphUri)
    {
        if (true === NamedNodeImpl::check($graphUri)) {
            $result = $this->query('SELECT (COUNT(*) AS ?count) FROM <' . $graphUri . '> WHERE {?s ?p ?o.}');
            
            return $result[0]['count'];

        } else {
            throw new \Exception('Parameter $graphUri is not valid or empty.');
        }
    }

    /**
     * Checks if a certain graph is available in the store.
     *
     * @param  string $graphUri URI of the graph to check if it is available.
     * @return boolean True if graph is available, false otherwise.
     * @todo   find a more precise way to check if a graph is available.
     */
    public function isGraphAvailable($graphUri)
    {
        return true === in_array($graphUri, $this->getAvailableGraphs());
    }
    
    /**
     * Establish a connection to the endpoint and authenticate.
     *
     * @return \Saft\Net\Client Setup HTTP client.
     */
    public function openConnection()
    {
        $this->client = new \Saft\Net\Client();
        
        $this->client->setUrl($this->adapterOptions['authUrl']);
        $this->client->sendDigestAuthentication(
            $this->adapterOptions['username'],
            $this->adapterOptions['password']
        );

        // validate CURL status
        if (curl_errno($this->client->getClient()->curl)) {
            throw new \Exception(curl_error($this->client->getClient()->error), 500);
        }
        
        $curlInfo = curl_getinfo($this->client->getClient()->curl);
    
        // If status code is 200, means everything is OK
        if (200 === $curlInfo['http_code']) {
            // save name of the store which provides SPARQL endpoint
            $this->storeName = $this->determineStoreOnServer($this->client->getClient()->response_headers);
            
            $this->client->setUrl($this->adapterOptions['queryUrl']);
            return $this->client;
        
        // validate HTTP status code (user/password credential issues)
        } else {
            throw new \Exception('Response with Status Code [' . $status_code . '].', 500);
        }
    }
    
    /**
     * This method sends a SPARQL query to the store.
     *
     * @param  string $query            The SPARQL query to send to the store.
     * @param  array  $options optional It contains key-value pairs and should provide additional
     *                                  introductions for the store and/or its adapter(s).
     * @return Result Returns result of the query. Depending on the query
     *                type, it returns either an instance of ResultIterator, StatementIterator, or ResultValue
     * @throws \Exception If query is no string.
     *                    If query is malformed.
     *                    If $options[resultType] = is neither extended nor array
     */
    public function query($query, array $options = array())
    {
        /**
         * result type not set, use array instead
         */
        if (false === isset($options['resultType'])) {
            // if nothing was set, array is default result type. Possible are: array, extended
            $options['resultType'] = 'array';

        /**
         * extended result type
         */
        } elseif ('array' != $options['resultType'] && 'extended' != $options['resultType']) {
            throw new \Exception('Given resultType is invalid, allowed are array and extended.');
        }

        $queryObject = new Query($query);

        /**
         * SPARQL query (usually to fetch data)
         */
        if (false === $queryObject->isUpdateQuery()) {
            $this->client->sendSparqlSelectQuery($query);
            
            if ('extended' == $options['resultType']) {
                $result = $this->client->sendSparqlSelectQuery($query);
                // TODO check result type automatically
                $return = json_decode($result, true);

            // array == $option['resultType']
            } else {
                $responseString = $this->client->sendSparqlSelectQuery($query);

                $return = array();

                // TODO check result type automatically
                $responseArray = json_decode($responseString, true);

                // in case $responseArray is null, something went wrong.
                if (null == $responseArray) {
                    throw new \Exception('SPARQL error: '. $responseString);
                } else {
                    foreach ($responseArray['results']['bindings'] as $entry) {
                        $returnEntry = array();

                        foreach ($responseArray['head']['vars'] as $var) {
                            $returnEntry[$var] = $entry[$var]['value'];
                        }

                        $return[] = $returnEntry;
                    }
                }
            }

        /**
         * SPARPQL Update query
         */
        } else {
            $this->client->sendSparqlUpdateQuery($query);
            $return = null;
        }

        return $return;
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
        
    }
}
