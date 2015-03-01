<?php

namespace Saft\Store\Adapter;

class Virtuoso extends \Saft\Store\Adapter\AbstractAdapter
{
    /**
     * Adapter option array
     *
     * @var array
     */
    protected $adapterOptions = null;

    /**
     * PDO ODBC
     * @var \PDO
     */
    protected $connection = null;

    /**
     * Constructor.
     *
     * @param  array $adapterOptions Array containing database credentials
     * @throws \Exception In case the pdo_odbc extension is not available
     * @todo Move init process and checks into its own functions
     */
    public function __construct(array $adapterOptions)
    {
        // check for odbc extension
        if (!extension_loaded("pdo_odbc")) {
            throw new \Exception(
                "Virtuoso adapter requires the PDO_ODBC extension to be loaded."
            );
            return;
        }

        $this->adapterOptions = $adapterOptions;

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
     * @return void
     * @throws \Exception
     */
    public function addGraph($graphUri)
    {
        $this->executeQuery("CREATE SILENT GRAPH <$graphUri>");
    }

    /**
     * Adds multiple triples to the graph.
     *
     * @param  string $graphUri URI of the graph to add the triples
     * @param  array  $triples  Array of triples to add.
     * @return \PDOStatement
     * @throw  \Exception
     */
    public function addMultipleTriples($graphUri, array $triples)
    {
        $tripleNumber = count($triples);

        /**
         * create batches
         */
        $batch = array();
        $batchSize = 50;

        for ($i = 0; $i < $tripleNumber; ++$i) {
            if (0 == $i % $batchSize) {
                $this->sparql(
                    "INSERT INTO GRAPH <". $graphUri ."> {" .
                    \Saft\Rdf\Triple::buildTripleString($batch) .
                    "}"
                );

                $batch = array();
            }
            $batch[] = $triples[$i];
        }

        $result = $this->sparql(
            "INSERT INTO GRAPH <". $graphUri ."> {" .
            \Saft\Rdf\Triple::buildTripleString($batch) .
            "}"
        );

        return $result;
    }

    /**
     * Add a triple.
     *
     * @param  string $graphUri  URI of the graph to add triple
     * @param  string $subject   URI of the subject to add
     * @param  string $predicate URI of the predicate to add
     * @param  array  $object    Array with data of the object to add
     * @return ODBC resource
     * @throw  \Exception
     */
    public function addTriple($graphUri, $subject, $predicate, array $object)
    {
        return $this->addMultipleTriples(
            $graphUri,
            array(array($subject, $predicate, $object))
        );
    }

    /**
     * Deletes all triples of a graph.
     *
     * @throw TODO Exceptions
     */
    public function clearGraph($graphUri)
    {
        $this->dropGraph($graphUri);
        $this->addGraph($graphUri);
    }

    /**
     * Closes a current connection to the database.
     *
     * @return void
     */
    public function closeConnection()
    {
        $this->connection = null;
    }

    /**
     * Drops a graph.
     *
     * @param  string $graphUri URI of the graph to remove
     * @throws \Exception
     */
    public function dropGraph($graphUri)
    {
        $this->sparql("DROP SILENT GRAPH <$graphUri>");
    }

    /**
     * Drops multiple triples.
     *
     * @param  string $graphUri URI of the graph to drop triples
     * @param  array  $triples  Array of triples to drop
     * @return ODBC resource Last used ODBC resource
     * @throws \Exception
     */
    public function dropMultipleTriples($graphUri, array $triples)
    {
        $tripleNumber = count($triples);

        /**
         * create batches
         */
        $batch = array();
        $batchSize = 250;

        for ($i = 0; $i < $tripleNumber; ++$i) {
            if (0 == $i % $batchSize) {
                // build SPARQL statement to delete triples
                $odbcRes = $this->sparql(
                    "DELETE FROM GRAPH <". $graphUri ."> {".
                    \Saft\Rdf\Triple::buildTripleString($batch) .
                    "}"
                );

                $batch = array();
            }
            $batch[] = $triples[$i];
        }

        $odbcRes = $this->sparql(
            "DELETE FROM GRAPH <". $graphUri ."> {".
            \Saft\Rdf\Triple::buildTripleString($batch) .
            "}"
        );

        return $odbcRes;
    }

    /**
     * Drops a triple.
     *
     * @param  string $graphUri  URI of the graph to drop triple
     * @param  string $subject   URI of the subject to drop
     * @param  string $predicate URI of the predicate to drop
     * @param  array  $object    Array with data of the object to drop
     * @return ODBC resource
     * @throw  \Exception
     */
    public function dropTriple($graphUri, $subject, $predicate, array $object)
    {
        return $this->dropMultipleTriples(
            $graphUri,
            array(array($subject, $predicate, $object))
        );
    }

    /**
     * Executes a SPARQL and SQL query on the database. Using PDO ODBC makes no
     * difference between SPARQL and SQL, because the result type is always an array.
     *
     * @param  string $queryString SPARQL- or SQL query to execute
     * @param  string $type        optional Set type of statement: sparql (standard), sparqlUpdate, sql.
     *                             sparqlUpdate, sql.
     * @param  string $graphUri    optional URI of the graph to execute the (SPARQL) query in.
     * @param  array  $variables   optional Key-values pairs to fill placeholders in the query.
     * @return \PDOStatement
     * @throw  \Exception If $queryString is invalid
     */
    public function executeQuery($queryString, $type = "sparql", $graphUri = null)
    {
        /**
         * SPARQL query (usually to fetch data)
         */
        if ("sparql" == $type) {
            if (true === \Saft\Rdf\NamedNode::check($graphUri)) {
                // enquote
                $graphUri = "'" . $graphUri . "'";
                $graphSpec = "define input:default-graph-uri <" . $graphUri . "> ";
            } else {
                // set Virtuoso NULL
                $graphUri = "NULL";
                $graphSpec = "";
            }

            // escape characters that delimit the query within the query
            $queryString = $graphSpec . "CALL DB.DBA.SPARQL_EVAL('" .
                           addcslashes($queryString, '\'\\') . "', '" .
                           $graphUri . "', 0)";

            /**
         * SPARPQL Update query
         */
        } elseif ("sparqlUpdate" == $type) {
            $queryString = "SPARQL " . $queryString;

            /**
         * SQL query
         */
        } else {
            // nothing to do
        }

        // execute query
        try {
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

    /**
     *
     */
    public function getAvailableGraphUris()
    {
        $query = $this->executeQuery(
            "SELECT ID_TO_IRI(REC_GRAPH_IID) AS graph ".
            "FROM DB.DBA.RDF_EXPLICITLY_CREATED_GRAPH",
            "sql"
        );

        $graphs = array();

        foreach ($query->fetchAll(\PDO::FETCH_ASSOC) as $graph) {
            $graphs[$graph["graph"]] = $graph["graph"];
        }

        return $graphs;
    }

    /**
     * Counts the number of triples in a graph.
     *
     * @param  string $graphUri URI of the graph you wanna count triples
     * @return integer Number of found triples
     * @throw  \Exception
     */
    public function getTripleCount($graphUri)
    {
        $result = $this->sparql(
            "SELECT COUNT(?s) as ?count FROM <$graphUri> WHERE {?s ?p ?o.}"
        );

        return $result[0]["count"];
    }

    /**
     * Checks if a certain graph is available in the store.
     *
     * @param  string $graphUri URI of the graph to check if it is available.
     * @return boolean True if graph is available, false otherwise.
     */
    public function isGraphAvailable($graphUri)
    {
        $graphs = $this->getAvailableGraphUris();

        return true === isset($graphs[$graphUri]);
    }

    /**
     * Returns the current connection resource.
     * The resource is created lazily if it doesn't exist.
     * @retun resource
     */
    public function openConnection()
    {
        // connection still closed
        if (!$this->connection) {
            $options = $this->adapterOptions;

            // check for dsn parameter
            if (!isset($options["dsn"])) {
                throw new \Exception("Parameter dsn is not set.");
            } else {
                $dsn = (string) $options["dsn"];
            }

            // check for username parameter
            if (!isset($options["username"])) {
                throw new \Exception("Parameter username is not set.");
            } else {
                $username = (string) $options["username"];
            }

            // check for password parameter
            if (!isset($options["password"])) {
                throw new \Exception("Parameter password is not set.");
            } else {
                $password = (string) $options["password"];
            }

            /**
             * Setup ODBC connection using PDO
             */
            try {
                $this->connection = new \PDO("odbc:" . $dsn, $username, $password);
                $this->connection->setAttribute(\PDO::ATTR_AUTOCOMMIT, false);
                $this->connection->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
                $this->connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            } catch (\PDOException $e) {
                throw new \Exception($e->getMessage());
            }

            $this->_user = $username;
        }

        return $this->connection;
    }

    /**
     * Send SPARQL query to the server.
     *
     * @param  string $query     Query to execute
     * @param  array  $variables optional Key-value-pairs to create prepared statements
     * @param  array  $options   optional Options to configure the query-execution and the result. result.
     *                                result.
     * @return array
     * @throw  \Exception
     */
    public function sparql($query, array $options = array())
    {
        /**
         * result type not set, use array instead
         */
        if (false === isset($options["resultType"])) {
            // if nothing was set, array is default result type
            // possible are: array, extended
            $options["resultType"] = "array";

            /**
         * extended result type
         */
        } elseif ("array" != $options["resultType"]
            && "extended" != $options["resultType"]
        ) {
            throw new \Exception(
                "Given resultType is invalid, allowed are array and extended."
            );
        }

        // prepare query
        $queryPrefix = "";
        if ("extended" == $options["resultType"]) {
            $queryPrefix = "define output:format \"JSON\"";
        }

        $sparqlQuery = $queryPrefix . PHP_EOL . (string) $query;
        $query = $this->executeQuery($sparqlQuery, "sparql");

        if (false !== $query) {
            $result = $query->fetchAll(\PDO::FETCH_ASSOC);

            // encode as JSON string
            if ("extended" === $options["resultType"]) {
                $result = current(current($result));
                $result = json_decode($result, true);
            }

            return $result;
        }
    }
}
