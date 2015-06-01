<?php

namespace Saft\Backend\HttpStore\Store;

use Saft\Backend\HttpStore\Net\Client;
use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\Statement;
use Saft\Rdf\StatementFactory;
use Saft\Rdf\StatementIterator;
use Saft\Rdf\StatementIteratorFactory;
use Saft\Rdf\Node;
use Saft\Rdf\NodeFactory;
use Saft\Rdf\NodeUtils;
use Saft\Sparql\Query\AbstractQuery;
use Saft\Sparql\Query\QueryFactory;
use Saft\Store\AbstractSparqlStore;
use Saft\Store\Store;
use Saft\Store\Result\ResultFactory;

/**
 * SparqlStore implementation of a client which handles store operations via HTTP. It is able to determine some
 * server types by checking response header.
 */
class Http extends AbstractSparqlStore
{
    /**
     * Adapter options
     *
     * @var array
     */
    protected $configuration = null;

    /**
     * @var Client
     */
    protected $client = null;

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @var QueryFactory
     */
    private $queryFactory;

    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /**
     * @var StatementFactory
     */
    private $statementFactory;

    /**
     * @var StatementIteratorFactory
     */
    private $statementIteratorFactory;

    /**
     * Constructor.
     *
     * @param NodeFactory              $nodeFactory
     * @param StatementFactory         $statementFactory
     * @param QueryFactory             $queryFactory
     * @param ResultFactory            $resultFactory
     * @param StatementIteratorFactory $statementIteratorFactory
     * @param array                    $configuration            Array containing database credentials
     * @throws \Exception              If HTTP store requires the PHP ODBC extension to be loaded.
     */
    public function __construct(
        NodeFactory $nodeFactory,
        StatementFactory $statementFactory,
        QueryFactory $queryFactory,
        ResultFactory $resultFactory,
        StatementIteratorFactory $statementIteratorFactory,
        array $configuration
    ) {
        $this->configuration = $configuration;

        $this->checkRequirements();

        // Open connection and, if possible, authenticate on server
        $this->openConnection();

        $this->nodeFactory = $nodeFactory;
        $this->statementFactory = $statementFactory;
        $this->queryFactory = $queryFactory;
        $this->resultFactory = $resultFactory;
        $this->statementIteratorFactory = $statementIteratorFactory;

        parent::__construct(
            $nodeFactory,
            $statementFactory,
            $queryFactory,
            $resultFactory,
            $statementIteratorFactory
        );
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        $this->closeConnection();
    }

    /**
     * Using digest authentication to authenticate user on the server.
     *
     * @param  string $authUrl  URL to authenticate.
     * @param  string $username Username to access.
     * @param  string $password Password to access.
     * @throws \Exception If response
     */
    protected function authenticateOnServer($authUrl, $username, $password)
    {
        $this->client->setUrl($authUrl);
        $this->client->sendDigestAuthentication($username, $password);

        $curlInfo = curl_getinfo($this->client->getClient()->curl);

        // If status code is not 200, something went wrong
        if (200 !== $curlInfo['http_code']) {
            throw new \Exception('Response with Status Code [' . $curlInfo['http_code'] . '].', 500);
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
     * Closes a current connection.
     */
    protected function closeConnection()
    {
        $this->client->getClient()->close();
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
     * @param  NamedNode $graph NamedNode instance of the graph you wanna count triples.
     * @return integer   Number of found triples
     */
    public function getTripleCount(NamedNode $graph)
    {
        $result = $this->query(
            'SELECT (COUNT(*) AS ?count) FROM <' . $graph->getUri() . '> WHERE {?s ?p ?o.}'
        );
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
        $graphs = $this->getGraphs();
        return isset($graphs[$graph->getUri()]);
    }

    /**
     * Establish a connection to the endpoint and authenticate.
     *
     * @return Client Setup HTTP client.
     */
    protected function openConnection()
    {
        $this->client = new Client();

        $configuration = array_merge(array(
            'authUrl' => '',
            'password' => '',
            'queryUrl' => '',
            'username' => ''
        ), $this->configuration);

        // authenticate only if an authUrl was given.
        if (NodeUtils::simpleCheckURI($configuration['authUrl'])) {
            $this->authenticateOnServer(
                $configuration['authUrl'],
                $configuration['username'],
                $configuration['password']
            );
        }

        // check query URL
        if (false === NodeUtils::simpleCheckUri($configuration['queryUrl'])) {
            throw new \Exception('Parameter queryUrl is not an URI or empty: '. $configuration['queryUrl']);
        }

        $this->client->setUrl($configuration['queryUrl']);
    }

    /**
     * This method sends a SPARQL query to the store.
     *
     * @param  string     $query            The SPARQL query to send to the store.
     * @param  array      $options optional It contains key-value pairs and should provide additional
     *                                      introductions for the store and/or its adapter(s).
     * @return Result     Returns result of the query. Its type depends on the type of the query.
     * @throws \Exception     If query is no string.
     * @throws \Exception     If query is malformed.
     * @throws StoreException If server returned an error.
     * @todo add support for DESCRIBE queries
     */
    public function query($query, array $options = array())
    {
        $queryObject = $this->queryFactory->createInstanceByQueryString($query);
        $queryParts = $queryObject->getQueryParts();

        /**
         * SPARQL query (usually to fetch data)
         */
        if ('selectQuery' == AbstractQuery::getQueryType($query)) {
            $resultArray = json_decode($this->client->sendSparqlSelectQuery($query), true);
            $entries = array();

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

                $entries[] = $newEntry;
            }

            $return = $this->resultFactory->createSetResult($entries);
            $return->setVariables($resultArray['head']['vars']);

        /**
         * SPARPQL Update query
         */
        } else {
            $result = $this->client->sendSparqlUpdateQuery($query);

            if ('askQuery' === AbstractQuery::getQueryType($query)) {
                $askResult = json_decode($result, true);

                if (true === isset($askResult['boolean'])) {
                    $return = $this->resultFactory->createValueResult($askResult['boolean']);

                // assumption here is, if a string was returned, something went wrong.
                } elseif (0 < strlen($result)) {
                    throw new StoreException($result);

                } else {
                    $return = $this->resultFactory->createEmptyResult();
                }
            } else {
                $return = $this->resultFactory->createEmptyResult();
            }
        }

        return $return;
    }
}
