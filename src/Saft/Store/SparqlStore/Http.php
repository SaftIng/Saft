<?php

namespace Saft\Store\SparqlStore;

use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\Statement;
use Saft\Rdf\StatementImpl;
use Saft\Sparql\Query;
use Saft\Store\AbstractSparqlStore;

/**
 * SparqlStore implementation of a client which handles store operations via HTTP.
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
     * Checks that all requirements for queries via HTTP are fullfilled.
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
        if (true === \Saft\Rdf\NamedNode::check($graphUri)) {
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
        // validate HTTP status code (user/password credential issues)
        $status_code = curl_getinfo($this->client->getClient()->curl, CURLINFO_HTTP_CODE);
        if ($status_code != 200) {
            throw new \Exception('Response with Status Code [' . $status_code . '].', 500);
        }

        $this->client->setUrl($this->adapterOptions['queryUrl']);

        return $this->client;
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
}
