<?php

namespace Saft\Backend\HttpStore\Store;

use Saft\Backend\HttpStore\Net\Client;
use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\Statement;
use Saft\Rdf\StatementFactory;
use Saft\Rdf\StatementIterator;
use Saft\Rdf\Node;
use Saft\Rdf\NodeFactory;
use Saft\Rdf\NodeUtils;
use Saft\Sparql\Query\AbstractQuery;
use Saft\Store\AbstractSparqlStore;
use Saft\Store\Store;
use Saft\Store\Result\ExceptionResult;
use Saft\Store\Result\EmptyResult;
use Saft\Store\Result\StatementResult;
use Saft\Store\Result\SetResult;
use Saft\Store\Result\ValueResult;

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
     * @var Client
     */
    protected $client = null;

    /**
     * Name of the store, which runs on the server, e.g. virtuoso.
     *
     * @var string
     */
    protected $storeName = '';

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @var NodeFactory
     */
    private $statementFactory;

    /**
     * Constructor.
     *
     * @param array $adapterOptions Array containing database credentials
     */
    public function __construct(NodeFactory $nodeFactory, StatementFactory $statementFactory, array $adapterOptions)
    {
        $this->adapterOptions = $adapterOptions;

        $this->checkRequirements();

        // Open connection
        $this->openConnection();

        $this->nodeFactory = $nodeFactory;
        $this->statementFactory = $statementFactory;

        parent::__construct($nodeFactory, $statementFactory);
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        $this->closeConnection();
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
     * Closes a current connection.
     */
    public function closeConnection()
    {
        $this->client->getClient()->close();
    }

    /**
     * Returns array with graphUri's which are available.
     *
     * @return array Array which contains graph URI's as values and keys.
     */
    public function getAvailableGraphs()
    {
        $result = $this->query('SELECT DISTINCT ?g WHERE { GRAPH ?g {?s ?p ?o.} }');
        $result = $result->getResultObject();

        $graphs = array();

        // $entry is of type NamedNode
        foreach ($result as $entry) {
            $graphs[$entry['g']->getUri()] = $entry['g']->getUri();
        }

        return $graphs;
    }

    /**
     * @return array Empty
     * TODO implement getStoreDescription
     */
    public function getStoreDescription()
    {
        return array();
    }

    /**
     * Counts the number of triples in a graph.
     *
     * @param  Node $graph URI of the graph you wanna count triples
     * @return integer Number of found triples
     * @throw  \Exception If parameter $graphUri is not valid or empty.
     */
    public function getTripleCount(Node $graph)
    {
        $result = $this->query('SELECT (COUNT(*) AS ?count) FROM <' . $graph->getUri() . '> WHERE {?s ?p ?o.}');
        $result = $result->getResultObject();

        return $result[0]['count']->getValue();
    }

    /**
     * Checks if a certain graph is available in the store.
     *
     * @param  Node $graph URI of the graph to check if it is available.
     * @return boolean True if graph is available, false otherwise.
     * @todo   find a more precise way to check if a graph is available.
     */
    public function isGraphAvailable(Node $graph)
    {
        return true === in_array($graph->getUri(), $this->getAvailableGraphs());
    }

    /**
     * Establish a connection to the endpoint and authenticate.
     *
     * @return Client Setup HTTP client.
     */
    public function openConnection()
    {
        $this->client = new Client();

        $adapterOptions = array_merge(array(
            'authUrl' => '',
            'password' => '',
            'queryUrl' => '',
            'username' => ''
        ), $this->adapterOptions);

        $this->client->setUrl($adapterOptions['authUrl']);
        $this->client->sendDigestAuthentication($adapterOptions['username'], $adapterOptions['password']);

        $curlInfo = curl_getinfo($this->client->getClient()->curl);

        // If status code is 200, means everything is OK
        if (200 === $curlInfo['http_code']) {
            $this->client->setUrl($adapterOptions['queryUrl']);
            return $this->client;

        // validate HTTP status code (user/password credential issues)
        } else {
            throw new \Exception('Response with Status Code [' . $curlInfo['http_code'] . '].', 500);
        }
    }

    /**
     * This method sends a SPARQL query to the store.
     *
     * @param  string $query            The SPARQL query to send to the store.
     * @param  array  $options optional It contains key-value pairs and should provide additional
     *                                  introductions for the store and/or its adapter(s).
     * @return Result Returns result of the query. Depending on the query type, it returns either an instance
     *                of EmptyResult, ExceptionResult, SetResult, StatementResult or ValueResult.
     * @throws \Exception If query is no string.
     *                    If query is malformed.
     * @todo add support for DESCRIBE queries
     * @todo current behavior only for Virtuoso, change that
     */
    public function query($query, array $options = array())
    {
        $queryObject = AbstractQuery::initByQueryString($query);
        $queryParts = $queryObject->getQueryParts();

        /**
         * SPARQL query (usually to fetch data)
         */
        if ('selectQuery' == AbstractQuery::getQueryType($query)) {
            $resultArray = json_decode($this->client->sendSparqlSelectQuery($query), true);

            $setResult = new SetResult();
            $setResult->setVariables($resultArray['head']['vars']);

            /**
             * go through all bindings and create according objects for SetResult instance.
             *
             * $bindingParts will look like:
             *
             * array(
             *      's' => array(
             *          'type' => 'uri',
             *          'value' => '...'
             *      ), ...
             * )
             */
            foreach ($resultArray['results']['bindings'] as $bindingParts) {
                $newEntry = array();

                /**
                 * A part looks like:
                 * array(
                 *      'type' => 'uri',
                 *      'value' => '...'
                 * )
                 */
                foreach ($bindingParts as $variable => $part) {
                    switch ($part['type']) {
                        /**
                         * Literal (language'd)
                         */
                        case 'literal':
                            $lang = null;
                            if (isset($part['xml:lang'])) {
                                $lang = $part['xml:lang'];
                            }

                            $newEntry[$variable] = $this->nodeFactory->createLiteral(
                                $part['value'],
                                'http://www.w3.org/1999/02/22-rdf-syntax-ns#langString',
                                $lang
                            );

                            break;

                        /**
                         * Typed-Literal
                         */
                        case 'typed-literal':
                            $newEntry[$variable] = $this->nodeFactory->createLiteral(
                                $part['value'],
                                $part['datatype']
                            );

                            break;

                        /**
                         * NamedNode
                         */
                        case 'uri':
                            $newEntry[$variable] = $this->nodeFactory->createNamedNode($part['value']);
                            break;

                        /**
                         * BlankNode
                         */
                        case 'bnode':
                            $newEntry[$variable] = $this->nodeFactory->createBlankNode($part['value']);
                            break;

                        default:
                            throw new \Exception('Unknown type given.');
                            break;
                    }
                }

                $setResult->append($newEntry);
            }

            $return = $setResult;

        /**
         * SPARPQL Update query
         */
        } else {
            $result = $this->client->sendSparqlUpdateQuery($query);

            if ('askQuery' === AbstractQuery::getQueryType($query)) {
                $askResult = json_decode($result, true);

                if (true === isset($askResult['boolean'])) {
                    $return = new ValueResult($askResult['boolean']);

                // if result-string starts with Virtuoso, we assume an error occour.
                } elseif ('Virtuoso' === substr($result, 0, 8)) {
                    $return = new ExceptionResult(new \Exception($result));

                } else {
                    $return = new EmptyResult();
                }
            } else {
                $return = new EmptyResult();
            }
        }

        return $return;
    }
}
