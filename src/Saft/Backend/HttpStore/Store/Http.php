<?php

namespace Saft\Backend\HttpStore\Store;

use Saft\Backend\HttpStore\Net\Client;
use Saft\Rdf\AbstractLiteral;
use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\LiteralImpl;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\Statement;
use Saft\Rdf\StatementImpl;
use Saft\Rdf\StatementIterator;
use Saft\Rdf\Node;
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
     * If set, all statement- and query related operations have to be in close collaboration with the
     * successor.
     *
     * @var instance which implements Saft\Store\StoreInterface.
     */
    protected $successor;

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
     * @param  Node $graph URI of the graph to create
     * @throws \Exception
     */
    public function addGraph(Node $graph)
    {
        $this->client->sendSparqlUpdateQuery('CREATE SILENT GRAPH <'. $graph->getUri() .'>');
    }

    /**
     * Adds multiple Statements to (default-) graph.
     *
     * @param  StatementIterator $statements          StatementList instance must contain Statement instances
     *                                                which are 'concret-' and not 'pattern'-statements.
     * @param  Node              $graph      optional Overrides target graph. If set, all statements will
     *                                                be add to that graph, if available.
     * @param  array             $options    optional It contains key-value pairs and should provide additional
     *                                                introductions for the store and/or its adapter(s).
     * @return boolean Returns true, if function performed without errors. In case an error occur, an exception
     *                 will be thrown.
     * @todo implement usage of graph inside the statement(s). create groups for each graph
     */
    public function addStatements(StatementIterator $statements, Node $graph = null, array $options = array())
    {
        // TODO migrate code to new interface
        $graphUri = null;
        if ($graph !== null) {
            $graphUri = $graph->getUri();
        }

        if ('virtuoso' == $this->storeName) {
            /*
             * Bcause Virtuoso wont accepts queries like:
             *
             *          INSERT DATA {Graph <...> {<...> <...> <...>}}
             *
             * so we have to change it to:
             *
             *          INSERT INTO GRAPH <...> {<...> <...> <...>.}
             *
             * 
             * Create batches out of given statements to improve statement throughput.
             */
            $counter = 0;
            $batchSize = 100;
            $batchStatements = array();

            foreach ($statements as $statement) {
                // we dont have to check, if $st is a valid Statement, because StatementIterator implementations
                // dont allow non-Statement entries.
                
                // non-concrete Statement instances not allowed
                if (false === $statement->isConcrete()) {
                    throw new \Exception('At least one Statement is not concrete');
                }
                
                // given $graphUri forces usage of it and not the graph from the statement instance
                if (null !== $graphUri) {
                    $graphUriToUse = $graphUri;

                // use graphUri from statement
                } else {
                    $graphUriToUse = $statement->getGraph()->getUri();
                }

                if (false === isset($batchStatements[$graphUriToUse])) {
                    $batchStatements[$graphUriToUse] = new ArrayStatementIteratorImpl(array());
                }

                /**
                 * Notice: add a triple to the batch, even a quad was given, because we dont want the quad
                 *         sparqlFormat call.
                 */
                $batchStatements[$graphUriToUse]->append(new StatementImpl(
                    $statement->getSubject(),
                    $statement->getPredicate(),
                    $statement->getObject()
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

            $result = true;

        } else {
            $result = parent::addStatements($statements, $graph, $options);
        }

        // if successor is set, ask it too.
        if ($this->successor instanceof Store) {
            $this->successor->addStatements($statements, $graph, $options);
        }

        return $result;
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
     * @param Node $graph URI of the graph to clear.
     * @throw TODO Exceptions
     */
    public function clearGraph(Node $graph)
    {
        $this->query('CLEAR GRAPH <' . $graph->getUri() . '>');
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
     * @param  Node      $graph     optional Overrides target graph. If set, all statements will be delete in
     *                                       that graph.
     * @param  array     $options   optional It contains key-value pairs and should provide additional
     *                                       introductions for the store and/or its adapter(s).
     * @return boolean Returns true, if function performed without errors. In case
     *                 an error occur, an exception will be thrown.
     */
    public function deleteMatchingStatements(Statement $statement, Node $graph = null, array $options = array())
    {
        // TODO migrate code to new interface
        $graphUri = null;
        if ($graph !== null) {
            $graphUri = $graph->getUri();
        }

        if ('virtuoso' == $this->storeName) {
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
            if (null === $graphUri) {
                $graphUri = $statement->getGraph();
            }

            // if given graphUri and $statements graph are both null, throw exception
            if (null === $graphUri) {
                throw new \Exception('Neither $graphUri nor $statement graph were set.');
            }

            $statementIterator = new ArrayStatementIteratorImpl(array($statement));
            $condition = $this->sparqlFormat($statementIterator);
            $query = 'WITH <'. $graphUri .'> DELETE {'. $condition .'} WHERE {'. $condition .'}';
            $this->query($query, $options);

            // if successor is set, ask it too.
            if ($this->successor instanceof Store) {
                $this->successor->deleteMatchingStatements($statement, $graph, $options);
            }

            $result = true;

        } else {
            $result = parent::deleteMatchingStatements($statement, $graph, $options);
        }

        // if successor is set, ask it too.
        if ($this->successor instanceof Store) {
            $this->successor->deleteMatchingStatements($statement, $graph, $options);
        }

        return $result;
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
     * @param  Node $graph URI of the graph to remove
     * @throws \Exception
     */
    public function dropGraph(Node $graph)
    {
        $this->client->sendSparqlUpdateQuery('DROP SILENT GRAPH <'. $graph->getUri() .'>');
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
     * It gets all statements of a given graph which match the following conditions:
     * - statement's subject is either equal to the subject of the same statement of the graph or it is null.
     * - statement's predicate is either equal to the predicate of the same statement of the graph or it is null.
     * - statement's object is either equal to the object of a statement of the graph or it is null.
     *
     * @param  Statement $statement          It can be either a concrete or pattern-statement.
     * @param  Node      $graph     optional Overrides target graph. If set, you will get all
     *                                       matching statements of that graph.
     * @param  array     $options   optional It contains key-value pairs and should provide additional
     *                                       introductions for the store and/or its adapter(s).
     * @return StatementIterator It contains Statement instances  of all matching
     *                           statements of the given graph.
     * @todo FILTER select
     * @todo check if graph URI is valid
     * @TODO make it dynamic to be able to do lazy loading
     */
    public function getMatchingStatements(Statement $statement, Node $graph = null, array $options = array())
    {
        // TODO migrate code to new interface
        $graphUri = null;
        if ($graph !== null) {
            $graphUri = $graph->getUri();
        }

        // if successor is set, ask it too.
        if ($this->successor instanceof Store) {
            $this->successor->getMatchingStatements($statement, $graph, $options);
        }

        if ('virtuoso' == $this->storeName) {
            // Remove, maybe available, graph from given statement and put it into an iterator.
            // reason for the removal of the graph is to avoid quads in the query. Virtuoso wants the graph
            // in the FROM part.
            $query = 'SELECT ?s ?p ?o ' .
                'FROM <'. $graphUri .'> '.
                'WHERE { ?s ?p ?o ';

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

            // execute query and save result
            // TODO transform getMatchingStatements into lazy loading, so a batch loading is possible
            $result = $this->query($query, $options);

            /**
             * Transform SetResult into StatementResult
             */
            $statementResult = new StatementResult();
            $statementResult->setVariables($result->getVariables());

            foreach ($result as $entry) {
                $statementList = array();
                $i = 0;
                foreach ($result->getVariables() as $variable) {
                    $statementList[$i++] = $entry[$variable];
                }
                $statementResult->append(
                    new StatementImpl($statementList[0], $statementList[1], $statementList[2])
                );
            }

            return $statementResult;

        } else {
            return parent::getMatchingStatements($statement, $graph, $options);
        }
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
     * Returns true or false depending on whether or not the statements pattern has any matches in the given
     * graph. It overrides AbstractSparqlStore's hasMatchingStatement in case the target store needs a different
     * query structure, such as Virtuoso.
     *
     * @param  Statement $Statement          It can be either a concrete or pattern-statement.
     * @param  Node      $graph     optional Overrides target graph.
     * @param  array     $options   optional It contains key-value pairs and should provide additional
     *                                       introductions for the store and/or its adapter(s).
     * @return boolean Returns true if at least one match was found, false otherwise.
     */
    public function hasMatchingStatement(Statement $Statement, Node $graph = null, array $options = array())
    {
        // TODO migrate code to new interface
        $graphUri = null;
        if ($graph !== null) {
            $graphUri = $graph->getUri();
        }

        // if successor is set, ask it too.
        if ($this->successor instanceof Store) {
            $this->successor->hasMatchingStatement($Statement, $graph, $options);
        }

        /**
         * Virtuoso
         */
        if ('virtuoso' === $this->storeName) {
            // set graphUri, use that from the statement if $graphUri is null
            if (null === $graphUri) {
                $graph = $Statement->getGraph();
                $graphUri = $graph->getUri();
            }

            if (false === NodeUtils::simpleCheckURI($graphUri)) {
                throw new \Exception('Neither $Statement has a valid graph nor $graphUri is valid URI.');
            }

            $statementIterator = new ArrayStatementIteratorImpl(array($Statement));
            $result = $this->query(
                'ASK FROM <'. $graphUri .'> { '. $this->sparqlFormat($statementIterator) .'}',
                $options
            );

            if (true === is_object($result)) {
                return $result->getResultObject();
            } else {
                return $result;
            }

        /**
         * Standard SPARQL
         */
        } else {
            return parent::hasMatchingStatement($statement, $graph, $options);
        }
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
                            $newEntry[$variable] = new LiteralImpl($part['value'], $part['xml:lang']);

                            break;
                        /**
                         * Typed-Literal
                         */
                        case 'typed-literal':
                            // get a value which has the same datatype as described in the given result
                            // e.g. xsd:string => "foo" instead of only foo
                            $newEntry[$variable] = NodeUtils::getRealValueBasedOnDatatype(
                                $part['datatype'],
                                $part['value']
                            );

                            break;

                        /**
                         * NamedNode
                         */
                        case 'uri':
                            $newEntry[$variable] = new NamedNodeImpl($part['value']);
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

    /**
     * Set successor instance. This method is useful, if you wanna build chain of instances which implement
     * StoreInterface. It sets another instance which will be later called, if a statement- or query-related
     * function gets called.
     * E.g. you chain a query cache and a virtuoso instance. In this example all queries will be handled by
     * the query cache first, but if no cache entry was found, the virtuoso instance gets called.
     *
     * @return array Array which contains information about the store and its features.
     */
    public function setChainSuccessor(Store $successor)
    {
        $this->successor = $successor;
    }
}
