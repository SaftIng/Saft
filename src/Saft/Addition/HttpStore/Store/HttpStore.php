<?php

/*
 * This file is part of Saft.
 *
 * (c) Konrad Abicht <hi@inspirito.de>
 * (c) Natanael Arndt <arndt@informatik.uni-leipzig.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Saft\Addition\HttpStore\Store;

use Curl\Curl;
use Saft\Rdf\StatementFactory;
use Saft\Rdf\StatementIteratorFactory;
use Saft\Rdf\Node;
use Saft\Rdf\NodeFactory;
use Saft\Rdf\RdfHelpers;
use Saft\Sparql\Result\Result;
use Saft\Sparql\Result\ResultFactory;
use Saft\Store\AbstractSparqlStore;

/**
 * SparqlStore implementation of a client which handles store operations via HTTP. It is able to determine some
 * server types by checking response header.
 */
class HttpStore extends AbstractSparqlStore
{
    /**
     * Adapter options.
     *
     * @var array
     */
    protected $configuration = null;

    /**
     * @var Curl\Curl
     */
    protected $httpClient = null;

    /**
     * @var NodeFactory
     */
    protected $nodeFactory;

    /**
     * @var RdfHelpers
     */
    protected $rdfHelpers;

    /**
     * @var ResultFactory
     */
    protected $resultFactory;

    /**
     * @var StatementFactory
     */
    protected $statementFactory;

    /**
     * @var StatementIteratorFactory
     */
    protected $statementIteratorFactory;

    /**
     * Constructor. Dont forget to call setClient and provide a working GuzzleHttp\Client instance.
     *
     * @param NodeFactory              $nodeFactory
     * @param StatementFactory         $statementFactory
     * @param ResultFactory            $resultFactory
     * @param StatementIteratorFactory $statementIteratorFactory
     * @param RdfHelpers               $rdfHelpers
     * @param array                    $configuration            Optional, array containing database credentials
     *
     * @throws \Exception if HTTP store requires the PHP ODBC extension to be loaded
     */
    public function __construct(
        NodeFactory $nodeFactory,
        StatementFactory $statementFactory,
        ResultFactory $resultFactory,
        StatementIteratorFactory $statementIteratorFactory,
        RdfHelpers $rdfHelpers,
        array $configuration = []
    ) {
        $this->RdfHelpers = $rdfHelpers;

        $this->configuration = $configuration;

        // Open connection and, if possible, authenticate on server
        $this->openConnection();

        $this->nodeFactory = $nodeFactory;
        $this->statementFactory = $statementFactory;
        $this->resultFactory = $resultFactory;
        $this->statementIteratorFactory = $statementIteratorFactory;
    }

    /**
     * Using digest authentication to authenticate user on the server.
     *
     * @param string $authUrl  URL to authenticate
     * @param string $username username to access
     * @param string $password password to access
     *
     * @throws \Exception If response has HTTP code other than 200
     */
    protected function authenticateOnServer(string $authUrl, string $username, string $password)
    {
        $response = $this->sendDigestAuthentication($authUrl, $username, $password);

        $httpCode = $response->getHttpCode();

        // If status code is not 200, something went wrong
        if (200 !== $httpCode) {
            throw new \Exception('Response with Status Code ['.$httpCode.'].', $httpCode);
        }
    }

    /**
     * @return Curl\Curl
     */
    public function getHttpClient(): Curl
    {
        return $this->httpClient;
    }

    /**
     * Establish a connection to the endpoint and authenticate.
     *
     * @return Client setup HTTP client
     */
    public function openConnection()
    {
        if (null == $this->httpClient) {
            return false;
        }

        $configuration = \array_merge([
            'auth-url' => '',
            'password' => '',
            'query-url' => '',
            'username' => '',
        ], $this->configuration);

        /*
         * authenticate only if an auth-url was given.
         */
        if ($this->RdfHelpers->simpleCheckURI($configuration['auth-url'])) {
            $this->authenticateOnServer(
                $configuration['auth-url'],
                $configuration['username'],
                $configuration['password']
            );
        }

        // check query URL
        if (false === isset($configuration['query-url'])
            || false === $this->rdfHelpers->simpleCheckURI($configuration['query-url'])) {
            throw new \Exception('$configuration field "query-url" is not an URI or empty: '.$configuration['query-url']);
        }
    }

    /**
     * This method sends a SPARQL query to the store.
     *
     * @param string $query   the SPARQL query to send to the store
     * @param array  $options optional It contains key-value pairs and should provide additional
     *                        introductions for the store and/or its adapter(s)
     *
     * @return Result Returns result of the query. Its type depends on the type of the query.
     *
     * @throws \Exception if an error during query processing occured
     */
    public function query(string $query, array $options = []): Result
    {
        $queryType = $this->getQueryType($query);

        /*
         * CONSTRUCT query
         */
        if ('construct' == $queryType) {
            $receivedResult = $this->sendSparqlSelectQuery($this->configuration['query-url'], $query);

            $resultArray = $this->transformResultToArray($receivedResult);

            if (isset($resultArray['results']['bindings'])) {
                if (0 < count($resultArray['results']['bindings'])) {
                    $statements = [];

                    // important: we assume the bindings list is ORDERED!
                    foreach ($resultArray['results']['bindings'] as $entries) {
                        $statements[] = $this->statementFactory->createStatement(
                            $this->transformEntryToNode($entries['s']),
                            $this->transformEntryToNode($entries['p']),
                            $this->transformEntryToNode($entries['o'])
                        );
                    }

                    return $this->resultFactory->createStatementResult($statements);
                }
            }

            return $this->resultFactory->createEmptyResult();

        /*
         * SELECT query
         */
        } elseif ('select' == $queryType) {
            $receivedResult = $this->sendSparqlSelectQuery($this->configuration['query-url'], $query);

            /*
             * invalid query or error found
             */
            if (\is_string($receivedResult)) {
                // nice up error message:
                // - make it a one liner
                // - remove multiple whitespaces
                $receivedResult = \str_replace(
                    [
                        PHP_EOL,
                        "\n"
                    ],
                    ' ',
                    $receivedResult
                );
                $receivedResult = preg_replace('/\s+/', ' ', $receivedResult);
                throw new \Exception($receivedResult);

            /*
             * no errors, compute result
             */
            } else {
                $resultArray = $this->transformResultToArray($receivedResult);

                $entries = [];

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
                    $newEntry = [];

                    foreach ($bindingParts as $variable => $part) {
                        $newEntry[$variable] = $this->transformEntryToNode($part);
                    }

                    $entries[] = $newEntry;
                }

                $return = $this->resultFactory->createSetResult($entries);
                $return->setVariables($resultArray['head']['vars']);

                return $return;
            }

        /*
         * SPARPQL ASK or Update query
         */
        } else { // update
            $receivedResult = $this->sendSparqlUpdateQuery($this->configuration['query-url'], $query);

            // transform object to array
            if (\is_object($receivedResult)) {
                $decodedResult = \json_decode(\json_encode($receivedResult), true);
            // transform json string to array
            } else {
                $decodedResult = \json_decode($receivedResult, true);
            }

            /*
             * ASK query
             */
            if ('ask' === $queryType) {
                if (true === isset($decodedResult['boolean'])) {
                    return $this->resultFactory->createValueResult($decodedResult['boolean']);

                // assumption here is, if a string was returned, something went wrong.
                } elseif (0 < \strlen($receivedResult)) {
                    throw new \Exception($receivedResult);
                } else {
                    return $this->resultFactory->createEmptyResult();
                }

            /*
             * Query failed:
             *
             * usually a SPARQL result does not return a string. if it does anyway, assume there is an error.
             */
            } elseif (null === $decodedResult && 0 < \strlen($receivedResult)) {
                throw new \Exception($receivedResult);

            /*
             * SPARQL UPDATE query, usually returns value
             */
            } else {
                return $this->resultFactory->createEmptyResult();
            }
        }
    }

    /**
     * Send digest authentication to the server via GET.
     *
     * @param string $username
     * @param string $password optional
     *
     * @return string
     */
    public function sendDigestAuthentication($url, $username, $password = null): string
    {
        $this->httpClient->setDigestAuthentication($username, $password);

        return $this->httpClient->get($url);
    }

    /**
     * Sends a SPARQL select query to the server.
     *
     * @param string $url
     * @param string $query
     *
     * @return string response of the POST request
     */
    public function sendSparqlSelectQuery($url, $query)
    {
        $this->httpClient->setHeader('Accept', 'application/sparql-results+json');
        $this->httpClient->setHeader('Content-Type', 'application/x-www-form-urlencoded');

        return $this->httpClient->post($url, ['query' => $query]);
    }

    /**
     * Sends a SPARQL update query to the server.
     *
     * @param string $url
     * @param string $query
     *
     * @return string response of the GET request
     */
    public function sendSparqlUpdateQuery($url, $query)
    {
        // TODO extend Accept headers to further formats
        $this->httpClient->setHeader('Accept', 'application/sparql-results+json');
        $this->httpClient->setHeader('Content-Type', 'application/sparql-update');

        return $this->httpClient->get($url, ['query' => $query]);
    }

    /**
     * @param \Curl\Curl $httpClient
     */
    public function setClient(Curl $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * Transforms server result to aray.
     *
     * @param mixed $receivedResult
     *
     * @return array
     */
    public function transformResultToArray($receivedResult)
    {
        // transform object to array
        if (\is_object($receivedResult)) {
            return \json_decode(\json_encode($receivedResult), true);
        // transform json string to array
        } else {
            return \json_decode($receivedResult, true);
        }
    }

    /**
     * Helper function which transforms an result entry to its proper Node instance.
     *
     * @param array $entry
     *
     * @return Node instance of Node
     *
     * @since 2.0.0
     */
    protected function transformEntryToNode(array $entry): Node
    {
        /*
         * An $entry looks like:
         * array(
         *      'type' => 'uri',
         *      'value' => '...'
         * )
         */

        // it seems that for instance Virtuoso returns type=literal for bnodes,
        // so we manually fix that here to avoid that problem, if other stores act
        // the same
        if (isset($entry['value'])
            && true === \is_string($entry['value'])
            && false !== \strpos($entry['value'], '_:')) {
            $entry['type'] = 'bnode';
        }

        $newEntry = null;

        switch ($entry['type']) {
            /*
             * Literal (language'd)
             */
            case 'literal':
                $lang = null;
                if (isset($entry['xml:lang'])) {
                    $lang = $entry['xml:lang'];
                }

                $newEntry = $this->nodeFactory->createLiteral(
                    $entry['value'],
                    'http://www.w3.org/1999/02/22-rdf-syntax-ns#langString',
                    $lang
                );

                break;

            /*
             * Typed-Literal
             */
            case 'typed-literal':
                $newEntry = $this->nodeFactory->createLiteral($entry['value'], $entry['datatype']);
                break;

            /*
             * NamedNode
             */
            case 'uri':
                $newEntry = $this->nodeFactory->createNamedNode($entry['value']);
                break;

            /*
             * BlankNode
             */
            case 'bnode':
                $newEntry = $this->nodeFactory->createBlankNode($entry['value']);
                break;

            default:
                throw new \Exception('Unknown type given: '.$entry['type']);
                break;
        }

        return $newEntry;
    }
}
