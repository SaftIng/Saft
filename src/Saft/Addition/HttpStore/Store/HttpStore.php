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
     * @param Curl                     $httpClient
     *
     * @throws \Exception if HTTP store requires the PHP ODBC extension to be loaded
     */
    public function __construct(
        NodeFactory $nodeFactory,
        StatementFactory $statementFactory,
        ResultFactory $resultFactory,
        StatementIteratorFactory $statementIteratorFactory,
        RdfHelpers $rdfHelpers,
        array $configuration = [],
        Curl $httpClient = null
    ) {
        $this->rdfHelpers = $rdfHelpers;
        $this->configuration = $configuration;
        $this->nodeFactory = $nodeFactory;
        $this->statementFactory = $statementFactory;
        $this->resultFactory = $resultFactory;
        $this->statementIteratorFactory = $statementIteratorFactory;

        // Open connection and, if possible, authenticate on server
        if (null == $httpClient) {
            $httpClient = new Curl();
            $httpClient->setOpt(\CURLOPT_FOLLOWLOCATION, true);
            $httpClient->setOpt(\CURLOPT_TIMEOUT, 10);
        }
        $this->httpClient = $httpClient;

        $this->openConnection();
    }

    /**
     * Adds multiple Statements to (default-) graph.
     *
     * @param StatementIterator|array $statements statementList instance must contain Statement instances which
     *                                            are 'concret-' and not 'pattern'-statements
     * @param Node                    $graph      Overrides target graph. If set, all statements will be add to
     *                                            that graph, if it is available. (optional)
     * @param array                   $options    Key-value pairs which provide additional introductions for the
     *                                            store and/or its adapter(s). (optional)
     *
     * @api
     *
     * @since 0.1
     */
    public function addStatements(iterable $statements, Node $graph = null, array $options = [])
    {
        if ($this->configuration)

        $graphUriToUse = null;

        /**
         * Create batches out of given statements to improve statement throughput.
         */
        $counter = 1;
        $batchSize = 100;
        $batchStatements = [];

        foreach ($statements as $statement) {
            // non-concrete Statement instances not allowed
            if (false === $statement->isConcrete()) {
                // We would need a rollback here, but we don't have any transactions so far
                throw new \Exception('At least one Statement is not concrete: '.$statement->toNTriples());
            }

            // given $graph forces usage of it and not the graph from the statement instance
            if ($graph instanceof NamedNode) {
                $graphUriToUse = $graph->getUri();
            // use graph from statement
            } elseif ($statement->getGraph() instanceof NamedNode) {
                $graphUriToUse = $statement->getGraph()->getUri();
            // no graph, therefore store decides
            } else {
                $graphUriToUse = null;
            }

            // init batch entry for the current graph URI, if not set yet.
            if (false === isset($batchStatements[$graphUriToUse])) {
                $batchStatements[$graphUriToUse] = [];
            }

            $batchStatements[$graphUriToUse][] = $statement;
        }

        /**
         * $batchStatements is an array with graphUri('s) as key(s) and iterator instances as value.
         * Each entry is related to a certain graph and contains a bunch of statement instances.
         */
        foreach ($batchStatements as $graphUriToUse => $batch) {
            $content = '';

            $graph = null;
            if (null !== $graphUriToUse) {
                $graph = $this->nodeFactory->createNamedNode($graphUriToUse);
            }

            foreach ($batch as $batchEntries) {
                $content .= $this->sparqlFormat(
                    $this->statementIteratorFactory->createStatementIteratorFromArray([$batchEntries]),
                    $graph
                ).' ';
            }

            $this->query(
                'INSERT {'.$content.'}
                WHERE {
                  SELECT * {
                    OPTIONAL { ?s ?p ?o . }
                  } LIMIT 1
                }',
                $options
            );
        }
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

        $httpCode = $this->httpClient->httpStatusCode;

        // If status code is not 200, something went wrong
        if (200 !== $httpCode) {
            throw new \Exception('Response with Status Code: '.$httpCode, $httpCode);
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
        $configuration = \array_merge([
            'query-url' => '',
            // auth related
            'password' => '',
            'username' => '',
        ], $this->configuration);

        /*
         * authenticate only if an auth-url was given.
         */
        if (!empty($configuration['username']) && !empty($configuration['password'])) {
            $this->useDigestAuthentication($configuration['username'], $configuration['password']);
        }

        // check query URL
        if (false === isset($configuration['query-url'])) {
            throw new \Exception('$configuration field "query-url" is not set: '.$configuration['query-url']);
        }
    }

    protected function niceUpErrorMessage(string $err): string
    {
        // nice up error message:
        // - make it a one liner
        // - remove multiple whitespaces
        $err = \str_replace(
            [
                PHP_EOL,
                "\n"
            ],
            ' ',
            $err
        );
        return preg_replace('/\s+/', ' ', $err);
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
                throw new \Exception($this->niceUpErrorMessage($receivedResult));

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
                throw new \Exception($this->niceUpErrorMessage($receivedResult));

            /*
             * SPARQL UPDATE query, usually returns value
             */
            } else {
                return $this->resultFactory->createEmptyResult();
            }
        }
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

        return $this->httpClient->get($url, ['query' => $query]);
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

        $result = $this->httpClient->get($url, ['query' => $query]);

        return $result;
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
                // only if a language was explicitly given, use related datatype URI ...
                if (isset($entry['xml:lang'])) {
                    $lang = $entry['xml:lang'];
                    $datatype = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#langString';
                // ... otherwise assume its a simple string
                } else {
                    $lang = null;
                    $datatype = 'http://www.w3.org/2001/XMLSchema#string';
                }

                $newEntry = $this->nodeFactory->createLiteral($entry['value'], $datatype, $lang);

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

    /**
     * Use digest authentication if you send queries to the server.
     *
     * @param string $username
     * @param string $password
     */
    public function useDigestAuthentication($username, $password)
    {
        $this->httpClient->setDigestAuthentication($username, $password);

        // if you use GET here, it seems to not work with CURL
        $this->httpClient->post($this->configuration['query-url']);

        if (200 != $this->httpClient->httpStatusCode) {
            throw new \Exception($this->httpClient->errorMessage);
        }
    }
}
