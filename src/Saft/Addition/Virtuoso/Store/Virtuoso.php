<?php

namespace Saft\Addition\Virtuoso\Store;

use Saft\Rdf\AbstractLiteral;
use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\Node;
use Saft\Rdf\NodeFactory;
use Saft\Rdf\NodeUtils;
use Saft\Rdf\Statement;
use Saft\Rdf\StatementFactory;
use Saft\Rdf\StatementIterator;
use Saft\Rdf\StatementIteratorFactory;
use Saft\Rdf\Triple;
use Saft\Sparql\Query\AbstractQuery;
use Saft\Sparql\Query\QueryFactory;
use Saft\Store\AbstractSparqlStore;
use Saft\Store\Store;
use Saft\Store\Exception\StoreException;
use Saft\Store\Result\EmptyResult;
use Saft\Store\Result\ResultFactory;
use Saft\Store\Result\SetResult;
use Saft\Store\Result\StatementResult;
use Saft\Store\Result\ValueResult;

/**
 * SparqlStore implementation of OpenLink Virtuoso. It supports version 6.1.8+
 */
class Virtuoso extends AbstractSparqlStore
{
    /**
     * Adapter option array which contains at least connection dsn, username and password.
     *
     * @var array
     */
    protected $configuration = null;

    /**
     * PDO ODBC
     *
     * @var \PDO
     */
    protected $connection = null;

    /**
     * @var NodeFactory
     */
    private $nodeFactory = null;

    /**
     * @var QueryFactory
     */
    private $queryFactory = null;

    /**
     * @var StatementFactory
     */
    private $statementFactory = null;

    /**
     * @var StatementIteratorFactory
     */
    private $statementIteratorFactory = null;

    /**
     * Constructor.
     *
     * @param NodeFactory              $nodeFactory
     * @param StatementFactory         $statementFactory
     * @param QueryFactory             $queryFactory
     * @param ResultFactory            $resultFactory
     * @param StatementIteratorFactory $statementIteratorFactory
     * @param array                    $adapterOptions           Array containing database credentials
     * @throws \Exception              If PHP ODBC extension was not loaded.
     * @throws \Exception              If PHP PDO_ODBC extension was not loaded.
     */
    public function __construct(
        NodeFactory $nodeFactory,
        StatementFactory $statementFactory,
        QueryFactory $queryFactory,
        ResultFactory $resultFactory,
        StatementIteratorFactory $statementIteratorFactory,
        array $configuration
    ) {
        $this->checkRequirements();

        $this->configuration = $configuration;

        // Open connection
        $this->openConnection();

        $this->nodeFactory = $nodeFactory;
        $this->statementFactory = $statementFactory;
        $this->queryFactory = $queryFactory;
        $this->resultFactory = $resultFactory;
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
     * Checks that all requirements for queries via HTTP are fullfilled.
     *
     * @return boolean True, if all requirements are fullfilled.
     * @throws \Exception If PHP ODBC extension was not loaded.
     * @throws \Exception If PHP PDO-ODBC extension was not loaded.
     */
    public function checkRequirements()
    {
        // check for odbc extension
        if (false === extension_loaded('odbc')) {
            throw new \Exception('Virtuoso store requires the PHP ODBC extension to be loaded.');

        // check for pdo_odbc extension
        } elseif (false === extension_loaded('pdo_odbc')) {
            throw new \Exception('Virtuoso store requires the PHP PDO_ODBC extension to be loaded.');
        }

        return true;
    }

    /**
     * Closes a current connection to the database.
     */
    protected function closeConnection()
    {
        $this->connection = null;
    }

    /**
     * Returns a list of all available graph URIs of the store. It can also respect access control,
     * to only returned available graphs in the current context. But that depends on the implementation
     * and can differ.
     *
     * @return array Simple array of key-value-pairs, which consists of graph URIs as key and NamedNode
     *               instance as value.
     */
    public function getGraphs()
    {
        $query = $this->sqlQuery(
            'SELECT ID_TO_IRI(REC_GRAPH_IID) AS graph FROM DB.DBA.RDF_EXPLICITLY_CREATED_GRAPH'
        );
        $graphs = array();
        foreach ($query->fetchAll(\PDO::FETCH_ASSOC) as $graph) {
            $graphs[$graph['graph']] = $this->nodeFactory->createNamedNode($graph['graph']);
        }
        return $graphs;
    }

    /**
     * @return array Empty array
     * @todo implement getStoreDescription
     */
    public function getStoreDescription()
    {
        return array();
    }

    /**
     * Checks if a certain graph is available in the store.
     *
     * @param  Node $graph URI of the graph to check if it is available.
     * @return boolean True if graph is available, false otherwise.
     */
    public function isGraphAvailable(Node $graph)
    {
        $graphs = $this->getGraphs();

        return isset($graphs[$graph->getUri()]);
    }

    /**
     * Returns the current connection resource. The resource is created lazily if it doesn't exist.
     *
     * @return \PDO Instance of \PDO representing an open PDO-ODBC connection.
     */
    protected function openConnection()
    {
        // connection still closed
        if (null === $this->connection) {
            // check for dsn parameter. it is usually the ODBC identifier, e.g. VOS.
            // for more information have a look into /etc/odbc.ini (*NIX systems)
            if (false === isset($this->configuration['dsn'])) {
                throw new \Exception('Parameter dsn is not set.');
            }

            // check for username parameter
            if (false === isset($this->configuration['username'])) {
                throw new \Exception('Parameter username is not set.');
            }

            // check for password parameter
            if (false === isset($this->configuration['password'])) {
                throw new \Exception('Parameter password is not set.');
            }

            /**
             * Setup ODBC connection using PDO-ODBC
             */
            try {
                $this->connection = new \PDO(
                    'odbc:' . (string)$this->configuration['dsn'],
                    (string)$this->configuration['username'],
                    (string)$this->configuration['password']
                );
                $this->connection->setAttribute(\PDO::ATTR_AUTOCOMMIT, false);
                $this->connection->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
                $this->connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            } catch (\PDOException $e) {
                throw new \Exception($e->getMessage());
            }
        }

        return $this->connection;
    }

    /**
     * This method sends a SPARQL query to the store.
     *
     * @param  string     $query            The SPARQL query to send to the store.
     * @param  array      $options optional It contains key-value pairs and should provide additional
     *                                      introductions for the store and/or its adapter(s).
     * @return Result     Returns result of the query. Its type depends on the type of the query.
     * @throws \Exception If query is no string.
     * @throws \Exception If query is malformed.
     * @throws \Exception If PDO query is false.
     * @todo handle multiple graphs in FROM clause
     */
    public function query($query, array $options = array())
    {
        $queryObject = $this->queryFactory->createInstanceByQueryString($query);
        $queryParts = $queryObject->getQueryParts();

        // if a non-graph query was given, we assume triples or quads. If neither quads nor triples were found,
        // throw an exception.
        if (false === $queryObject->isGraphQuery()
            && false === isset($queryParts['triple_pattern'])
            && false === isset($queryParts['quad_pattern'])) {
            throw new \Exception('Non-graph queries must have triples or quads.');
        }

        /**
         * SPARQL query (usually to fetch data)
         */
        if ('selectQuery' === AbstractQuery::getQueryType($query)) {
            // force extended result to have detailed information about given result entries, such as datatype and
            // language information.
            $sparqlQuery = 'define output:format "JSON"' . PHP_EOL . $query;

            // escape characters that delimit the query within the query using addcslashes
            $graphUri = 'NULL';
            $graphSpec = '';
            // escape characters that delimit the query within the query
            $sparqlQuery = $graphSpec . 'CALL DB.DBA.SPARQL_EVAL(\''. addcslashes($sparqlQuery, '\'\\') . '\', '.
                           '\''. $graphUri . '\', 0)';

            // execute query
            try {
                $pdoQuery = $this->connection->prepare(
                    $sparqlQuery,
                    array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY)
                );

                $pdoQuery->execute();

            } catch (\PDOException $e) {
                throw new StoreException($e->getMessage());
            }

            $entries = array();

            // transform result to array in case we fired a non-UPDATE query
            if (false !== $pdoQuery) {
                $resultArray = json_decode(current(current($pdoQuery->fetchAll(\PDO::FETCH_ASSOC))), true);

                $variables = $resultArray['head']['vars'];

                // in case the result was empty, Virtuoso does not return a list of variables, which are
                // usually located in the SELECT part. so we try to extract the variables by ourselves.
                if (0 == count($variables)) {
                    $variables = $queryParts['variables'];
                }

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
                                $newEntry[$variable] = $this->nodeFactory->createLiteral(
                                    $part['value'],
                                    'http://www.w3.org/1999/02/22-rdf-syntax-ns#langString',
                                    $part['xml:lang']
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

                            default:
                                throw new \Exception('Unknown type given.');
                                break;
                        }
                    }

                    $entries[] = $newEntry;
                }

                $setResult = $this->resultFactory->createSetResult(new \ArrayIterator($entries));
                $setResult->setVariables($variables);
                return $setResult;

            } else {
                throw new \Exception('PDO query is false.');
            }

        /**
         * SPARPQL Update query
         */
        } else {
            $sparqlQuery = 'SPARQL ' . $query;

            // execute query
            try {
                $pdoQuery = $this->connection->prepare(
                    $sparqlQuery,
                    array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY)
                );

                $pdoQuery->execute();

            } catch (\PDOException $e) {
                throw new \Exception($e->getMessage());
            }

            // ask result
            if ('askQuery' === AbstractQuery::getQueryType($query)) {
                $pdoResult = $pdoQuery->fetchAll(\PDO::FETCH_ASSOC);
                return $this->resultFactory->createValueResult(true !== empty($pdoResult));
            } else {
                return $this->resultFactory->createEmptyResult();
            }
        }
    }

    /**
     * Executes a SQL query on the database.
     *
     * @param  string        $queryString SPARQL- or SQL query to execute
     * @return \PDOStatement Instance of PDOStatement which contains the result of the previous query.
     * @throws \Exception    If $queryString is invalid
     */
    public function sqlQuery($queryString)
    {
        try {
            // execute query
            $query = $this->connection->prepare(
                $queryString,
                array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY)
            );
            $query->execute();
            return $query;
        } catch (\PDOException $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
