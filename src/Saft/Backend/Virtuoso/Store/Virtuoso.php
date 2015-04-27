<?php

namespace Saft\Backend\Virtuoso\Store;

use Saft\Rdf\AbstractLiteral;
use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\LiteralImpl;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\Statement;
use Saft\Rdf\StatementImpl;
use Saft\Rdf\StatementIterator;
use Saft\Rdf\Triple;
use Saft\Rdf\Node;
use Saft\Rdf\NodeUtils;
use Saft\Sparql\Query\AbstractQuery;
use Saft\Store\AbstractSparqlStore;
use Saft\Store\Store;
use Saft\Store\Result\EmptyResult;
use Saft\Store\Result\ExceptionResult;
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
     * If set, all statement- and query related operations have to be in close collaboration with the
     * successor.
     *
     * @var instance which implements Saft\Store\StoreInterface.
     */
    protected $successor;

    /**
     * Constructor.
     *
     * @param  array $configuration Array containing database credentials
     * @throws \Exception In case the PHP's odbc or pdo_odbc extension is not available
     */
    public function __construct(array $configuration)
    {
        $this->checkRequirements();

        $this->configuration = $configuration;

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
     * Adds a new empty and named graph.
     *
     * @param  Node $graphUri URI of the graph to create.
     * @throws \Exception
     */
    public function addGraph(Node $graph)
    {
        $this->query('CREATE SILENT GRAPH <'. $graph->getUri() .'>');
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

        foreach ($statements as $statement) {
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
             *         sparqlFormat call, because Virtuoso wont accepts queries like:
             *
             *          INSERT DATA {Graph <> {...}}
             *
             *         so we have to change it to:
             *
             *          INSERT INTO GRAPH <> {<...> <...> <...>. ...}
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

        // if successor is set, ask it too.
        if ($this->successor instanceof Store) {
            $this->successor->addStatements($statements, $graph, $options);
        }

        return true;
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
     * Deletes all triples of a graph.
     *
     * @throws TODO Exceptions
     */
    public function clearGraph(Node $graph)
    {
        $this->dropGraph($graph);
        $this->addGraph($graph);
    }

    /**
     * Closes a current connection to the database.
     */
    protected function closeConnection()
    {
        $this->connection = null;
    }

    /**
     * Removes all statements from a (default-) graph which match with given statement.
     *
     * @param  Statement $statement          It can be either a concrete or pattern-statement.
     * @param  Node      $graph     optional Overrides target graph. If set, all statements will be delete in
     *                                       that graph.
     * @param  array     $options   optional It contains key-value pairs and should provide additional
     *                                       introductions for the store and/or its adapter(s).
     * @return boolean Returns true, if function performed without errors. In case an error occur, an exception
     *                 will be thrown.
     */
    public function deleteMatchingStatements(Statement $statement, Node $graph = null, array $options = array())
    {
        // TODO migrate code to new interface
        $graphUri = null;
        if ($graph !== null) {
            $graphUri = $graph->getUri();
        }

        /**
         * At least Virtuoso 6.1.8 does not understand DELETE DATA calls containing graph and variables such as:
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

        // if successor is set, ask it too.
        if ($this->successor instanceof Store) {
            $this->successor->deleteMatchingStatements($statement, $graph, $options);
        }

        return true;
    }

    /**
     * Drops an existing graph.
     *
     * @param string $graphUri          URI of the graph to drop.
     * @param array  $options  optional It contains key-value pairs and should provide additional introductions
     *                                  for the store and/or its adapter(s).
     * @throw \Exception
     */
    public function dropGraph(Node $graph, array $options = array())
    {
        $this->query('DROP SILENT GRAPH <'. $graph->getUri() .'>');
    }

    /**
     * Returns array with graphUri's which are available.
     *
     * @return array Array which contains graph URI's as values and keys.
     */
    public function getAvailableGraphs()
    {
        $query = $this->sqlQuery(
            'SELECT ID_TO_IRI(REC_GRAPH_IID) AS graph FROM DB.DBA.RDF_EXPLICITLY_CREATED_GRAPH'
        );

        $graphs = array();

        foreach ($query->fetchAll(\PDO::FETCH_ASSOC) as $graph) {
            $graphs[$graph['graph']] = $graph['graph'];
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
     * @todo make it possible to read graphUri from $statement, if given $graphUri is null
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
     * Counts the number of triples in a graph.
     *
     * @param  Node  $graph URI of the graph you wanna count triples
     * @return integer Number of found triples
     */
    public function getTripleCount(Node $graph)
    {
        $result = $this->query('SELECT (COUNT(*) AS ?count) FROM <' . $graph->getUri() . '> WHERE {?s ?p ?o.}');
        $result = $result->getResultObject();

        return $result[0]['count']->getValue();
    }

    /**
     * Returns true or false depending on whether or not the statements pattern has any matches in the given
     * graph. It overrides AbstractSparqlStore's hasMatchingStatement, because Virtuoso needs the graph URI
     * outside the braces and not within the condition, such as ASK Graph <http://foo/> { ... }
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
    }

    /**
     * Checks if a certain graph is available in the store.
     *
     * @param  Node $graph URI of the graph to check if it is available.
     * @return boolean True if graph is available, false otherwise.
     */
    public function isGraphAvailable(Node $graph)
    {
        $graphs = $this->getAvailableGraphs();

        return true === isset($graphs[$graph->getUri()]);
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
     * @param  string $query            The SPARQL query to send to the store.
     * @param  array  $options optional It contains key-value pairs and should provide additional introductions
     *                                  for the store and/or its adapter(s).
     * @return Result Returns result of the query. Depending on the query type, it returns either an instance
     *                of EmptyResult, ExceptionResult, SetResult, StatementResult or ValueResult.
     * @throws \Exception If query is no string.
     * @throws \Exception If query is malformed.
     * @throws \Exception If PDO query is false.
     * @todo handle multiple graphs in FROM clause
     */
    public function query($query, array $options = array())
    {
        $queryObject = AbstractQuery::initByQueryString($query);
        $queryParts = $queryObject->getQueryParts();

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
                return new ExceptionResult($e);
            }

            // if successor is set, ask it too.
            if ($this->successor instanceof Store) {
                $this->successor->query($query, $options);
            }

            $setResult = new SetResult();

            // transform result to array in case we fired a non-UPDATE query
            if (false !== $pdoQuery) {
                $resultArray = json_decode(current(current($pdoQuery->fetchAll(\PDO::FETCH_ASSOC))), true);

                $variables = $resultArray['head']['vars'];

                // in case the result was empty, Virtuoso does not return a list of variables, which are
                // usually located in the SELECT part. so we try to extract the variables by ourselves.
                if (0 == count($variables)) {
                    $variables = $queryParts['variables'];
                }

                $setResult->setVariables($variables);

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
                                // interprete some datatypes to convert the value to the corresponding type
                                // e.g. xsd:int => "5" will become a PHP-integer 5
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
                return new ValueResult(true !== empty($pdoResult));
            } else {
                return new EmptyResult();
            }
        }
    }

    /**
     * Executes a SQL query on the database.
     *
     * @param  string $queryString SPARQL- or SQL query to execute
     * @return \PDOStatement
     * @throws \Exception If $queryString is invalid
     */
    public function sqlQuery($queryString)
    {
        try {
            // execute query
            $query = $this->connection->prepare($queryString, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY));

            $query->execute();

            return $query;

        } catch (\PDOException $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
