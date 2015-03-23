<?php

namespace Saft\Store\SparqlStore;

use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\Statement;
use Saft\Rdf\StatementImpl;
use Saft\Rdf\StatementIterator;
use Saft\Rdf\Triple;
use Saft\Sparql\Query;
use Saft\Store\AbstractSparqlStore;
use Saft\Store\StoreInterface;

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
     * @param  string $graphUri URI of the graph to create.
     * @throws \Exception
     */
    public function addGraph($graphUri, array $options = array())
    {
        $this->query('CREATE SILENT GRAPH <'. $graphUri .'>');
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
                $graphUriToUse = $statement->getGraph()->getValue();
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
        if ($this->successor instanceof StoreInterface) {
            $this->successor->addStatements($statements, $graphUri, $options);
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
    public function clearGraph($graphUri)
    {
        $this->dropGraph($graphUri);
        $this->addGraph($graphUri);
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
     * @param  string    $graphUri  optional Overrides target graph. If set, all statements will be delete in
     *                                       that graph.
     * @param  array     $options   optional It contains key-value pairs and should provide additional
     *                                       introductions for the store and/or its adapter(s).
     * @return boolean Returns true, if function performed without errors. In case an error occur, an exception 
     *                 will be thrown.
     */
    public function deleteMatchingStatements(Statement $statement, $graphUri = null, array $options = array())
    {
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
        if ($this->successor instanceof StoreInterface) {
            $this->successor->deleteMatchingStatements($statement, $graphUri, $options);
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
    public function dropGraph($graphUri, array $options = array())
    {
        $this->query('DROP SILENT GRAPH <'. $graphUri .'>');
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
     * @param  string    $graphUri  optional Overrides target graph. If set, you will get all
     *                                       matching statements of that graph.
     * @param  array     $options   optional It contains key-value pairs and should provide additional
     *                                       introductions for the store and/or its adapter(s).
     * @return StatementIterator It contains Statement instances  of all matching
     *                           statements of the given graph.
     * @todo FILTER select
     * @todo check if graph URI is valid
     */
    public function getMatchingStatements(Statement $statement, $graphUri = null, array $options = array())
    {
        // if successor is set, ask it too.
        if ($this->successor instanceof StoreInterface) {
            $this->successor->getMatchingStatements($statement, $graphUri, $options);
        }
        
        // Remove, maybe available, graph from given statement and put it into an iterator
        // reason for the removal of the graph is to avoid quads in the query. Virtuoso wants the graph in the
        // FROM part.
        $statementIterator = new ArrayStatementIteratorImpl(array(
            new StatementImpl(
                $statement->getSubject(),
                $statement->getPredicate(),
                $statement->getObject()
            )
        ));
        
        return $this->query(
            'SELECT * FROM <'. $graphUri .'> WHERE {'. $this->sparqlFormat($statementIterator) .'}',
            $options
        );
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
     * @param  string  $graphUri URI of the graph you wanna count triples
     * @return integer Number of found triples
     * @throws \Exception
     */
    public function getTripleCount($graphUri)
    {
        $result = $this->query('SELECT COUNT(?s) as ?count FROM <'. $graphUri .'> WHERE {?s ?p ?o.}');

        return $result[0]['count'];
    }
    
    /**
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
        // if successor is set, ask it too.
        if ($this->successor instanceof StoreInterface) {
            $this->successor->hasMatchingStatement($statement, $graphUri, $options);
        }

        return parent::hasMatchingStatement($statement, $graphUri, $options);
    }

    /**
     * Checks if a certain graph is available in the store.
     *
     * @param  string $graphUri URI of the graph to check if it is available.
     * @return boolean True if graph is available, false otherwise.
     */
    public function isGraphAvailable($graphUri)
    {
        $graphs = $this->getAvailableGraphs();

        return true === isset($graphs[$graphUri]);
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
     * @param  array  $options optional It contains key-value pairs and should provide additional
     *                                  introductions for the store and/or its adapter(s).
     * @return Result Returns result of the query. Depending on the query
     *                type, it returns either an instance of ResultIterator, StatementIterator, or ResultValue
     * @throws \Exception If query is no string.
     * @throws \Exception If query is malformed.
     * @throws \Exception If $options[resultType] = is neither extended nor array
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

        // prepare query
        $queryPrefix = '';
        if ('extended' == $options['resultType']) {
            $queryPrefix = 'define output:format "JSON"';
        }

        $queryObject = new Query($query);
        $sparqlQuery = $queryPrefix . PHP_EOL . $query;

        /**
         * SPARQL query (usually to fetch data)
         */
        if (false === in_array($queryObject->getType(), array('insertData', 'insertInto', 'deleteData', 'delete'))) {
            $graphUri = 'NULL';
            $graphSpec = '';

            // escape characters that delimit the query within the query
            $sparqlQuery = $graphSpec . 'CALL DB.DBA.SPARQL_EVAL(\''.
                           addcslashes($sparqlQuery, '\'\\') . '\', \''.
                           $graphUri . '\', 0)';

        /**
         * SPARPQL Update query
         */
        } else {
            $sparqlQuery = 'SPARQL ' . $sparqlQuery;
        }

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
        
        // if successor is set, ask it too.
        if ($this->successor instanceof StoreInterface) {
            $this->successor->query($query, $options);
        }

        // transform result to array in case we fired a non-UPDATE query
        if (false !== $pdoQuery) {
            $result = $pdoQuery->fetchAll(\PDO::FETCH_ASSOC);
            
            // if it was an ASK query, return true or false.
            if ('ask' === $queryObject->getType()) {
                // TODO fix that ASK queries return true even the graph is empty
                return '1' === $result[0]['__ask_retval'];
                
            // encode as JSON string
            } elseif ('extended' === $options['resultType']) {
                $result = current(current($result));
                $result = json_decode($result, true);
            }

            return $result;
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
