<?php

namespace Saft\Addition\ARC2\Store;

use Saft\Rdf\NamedNode;
use Saft\Rdf\Node;
use Saft\Rdf\NodeFactory;
use Saft\Rdf\Statement;
use Saft\Rdf\StatementFactory;
use Saft\Rdf\StatementIterator;
use Saft\Rdf\StatementIteratorFactory;
use Saft\Sparql\Query\AbstractQuery;
use Saft\Sparql\Query\QueryFactory;
use Saft\Store\AbstractSparqlStore;
use Saft\Store\Result\EmptyResult;
use Saft\Store\Result\ResultFactory;
use Saft\Store\Result\SetResult;
use Saft\Store\Result\ValueResult;

class ARC2 extends AbstractSparqlStore
{
    /**
     * Adapter option array which contains at host, username, password and database.
     *
     * @var array
     */
    protected $configuration = null;

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
     * @var ARC2_Store
     */
    protected $store;

    /**
     * Constructor.
     *
     * @param NodeFactory              $nodeFactory
     * @param StatementFactory         $statementFactory
     * @param QueryFactory             $queryFactory
     * @param ResultFactory            $resultFactory
     * @param StatementIteratorFactory $statementIteratorFactory
     * @param array                    $configuration Array containing database credentials
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

        // Open connection
        $this->openConnection();

        // Check if the store is setup. If not, create missing MySQL tables.
        if (!$this->store->isSetUp()) {
            $this->store->setUp();
        }

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
     * Adds multiple Statements to (default-) graph. It overrides parents addStatements because ARC2 only
     * supports SPARQL+ and not SPARQL Update 1.1, which means an INSERT INTO query has to look like:
     * INSERT INTO <http://graph/> { triple ... }.
     *
     * @param  StatementIterator|array $statements       StatementList instance must contain Statement
     *                                                   instances which are 'concret-' and not
     *                                                   'pattern'-statements.
     * @param  Node                    $graph   optional Overrides target graph. If set, all statements
     *                                                   will be add to that graph, if it is available.
     * @param  array                   $options optional Key-value pairs which provide additional
     *                                                   introductions for the store and/or its
     *                                                   adapter(s).
     */
    public function addStatements($statements, Node $graph = null, array $options = array())
    {
        $graphUriToUse = null;

        /**
         * Create batches out of given statements to improve statement throughput.
         */
        $counter = 0;
        $batchSize = 100;
        $batchStatements = array();

        foreach ($statements as $statement) {
            // non-concrete Statement instances not allowed
            if (false === $statement->isConcrete()) {
                throw new \Exception('At least one Statement is not concrete');
            }

            // given $graph forces usage of it and not the graph from the statement instance
            if (null !== $graph) {
                $graphUriToUse = $graph->getUri();
                // reuse $graph instance later on.

            // use graphUri from statement
            } elseif (null !== $statement->getGraph()) {
                $graph = $statement->getGraph();
                $graphUriToUse = $graph->getUri();

            // no graph instance was found
            }

            // init batch entry for the current graph URI, if not set yet.
            if (false === isset($batchStatements[$graphUriToUse])) {
                $batchStatements[$graphUriToUse] = array();
            }

            $batchStatements[$graphUriToUse][] = $this->statementFactory->createStatement(
                $statement->getSubject(),
                $statement->getPredicate(),
                $statement->getObject()
            );

            // after batch is full, execute collected statements all at once
            if (0 === $counter % $batchSize) {
                /**
                 * $batchStatements is an array with graphUri('s) as key(s) and iterator instances as value
                 * Each entry is related to a certain graph and contains a bunch of statement instances.
                 */
                foreach ($batchStatements as $graphUriToUse => $statementBatch) {
                    $this->query(
                        'INSERT INTO <'. $graphUriToUse .'> {'.
                        $this->sparqlFormat(
                            $this->statementIteratorFactory->createIteratorFromArray(
                                $statementBatch
                            )
                        ) .'}',
                        $options
                    );
                }

                // re-init variables
                $batchStatements = array();
            }
        }
    }

    /**
     * Create a new graph with the URI given as Node. If the underlying store implementation doesn't
     * support empty graphs this method will have no effect.
     *
     * @param  NamedNode  $graph            Instance of NamedNode containing the URI of the graph to create.
     * @param  array      $options optional It contains key-value pairs and should provide additional
     *                                      introductions for the store and/or its adapter(s).
     * @throws \Exception If given $graph is not a NamedNode.
     * @throws \Exception If the given graph could not be created.
     */
    public function createGraph(NamedNode $graph, array $options = array())
    {
        if ($graph->isNamed()) {
            // table names
            $g2t = $this->configuration['table-prefix'] . '_g2t';
            $id2val = $this->configuration['table-prefix'] . '_id2val';

            /*
             * for id2val table
             */
            // generate new id based on the number of existing rows + 1
            // TODO change that to be more save: create pull request to ARC2 with:
            // - either add primary key auto increment
            // - or change table type to innodb to have lock-on-write
            $newIdid2val = 1 + $this->getRowCount($id2val);

            $query = 'INSERT INTO '. $id2val .' (id, val) VALUES('. $newIdid2val .', "'. $graph->getUri() .'")';
            $this->store->queryDB($query, $this->store->getDBCon());

            /*
             * for id2val table
             */
            $newIdg2t = 1 + $this->getRowCount($g2t);
            $query = 'INSERT INTO '. $g2t .' (g, t) VALUES('. $newIdg2t .', "'. $newIdid2val .'")';
            $this->store->queryDB($query, $this->store->getDBCon());

        } else {
            throw new \Exception('Given $graph is not a NamedNode.');
        }
    }

    /**
     * Removes all statements from a (default-) graph which match with given statement.
     *
     * @param  Statement $statement          It can be either a concrete or pattern-statement.
     * @param  Node      $graph     optional Overrides target graph. If set, all statements will
     *                                       be delete in that graph.
     * @param  array     $options   optional Key-value pairs which provide additional introductions
     *                                       for the store and/or its adapter(s).
     */
    public function deleteMatchingStatements(
        Statement $statement,
        Node $graph = null,
        array $options = array()
    ) {
        // given $graph forces usage of it and not the graph from the statement instance
        if (null !== $graph) {
            // use given $graph

        // use graphUri from statement
        } elseif (null === $graph && null !== $statement->getGraph()) {
            $graph = $statement->getGraph();
        }

        // create triple statement, because we have to handle the graph extra
        $tripleStatement = $this->statementFactory->createStatement(
            $statement->getSubject(),
            $statement->getPredicate(),
            $statement->getObject()
        );

        $statementIterator = $this->statementIteratorFactory->createIteratorFromArray(
            array($tripleStatement)
        );

        $triple = $this->sparqlFormat($statementIterator);
        $query = 'DELETE ';
        if (null !== $graph) {
            $query .= 'FROM <'. $graph->getUri() .'> ';
        }
        $query .= '{'. $triple .'} WHERE {'. $triple .'}';

        $this->query($query);
    }

    /**
     * Removes the given graph from the store.
     *
     * @param  NamedNode  $graph            Instance of NamedNode containing the URI of the graph to drop.
     * @param  array      $options optional It contains key-value pairs and should provide additional
     *                                      introductions for the store and/or its adapter(s).
     * @throws \Exception If given $graph is not a NamedNode.
     * @throws \Exception If the given graph could not be droped
     */
    public function dropGraph(NamedNode $graph, array $options = array())
    {
        if ($graph->isNamed()) {
            $this->store->queryDB('DELETE FROM <'. $graph->getUri() .'>', $this->store->getDBCon());
        } else {
            throw new \Exception('Given $graph is not a NamedNode.');
        }
    }

    /**
     * Empties all ARC2-related tables from the database.
     */
    public function emptyAllTables()
    {
        $this->store->reset();
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
        $g2t = $this->configuration['table-prefix'] . '_g2t';
        $id2val = $this->configuration['table-prefix'] . '_id2val';

        // collects all values which have an ID (column g) in the g2t table.
        $query = 'SELECT id2val.val AS graphUri
            FROM '. $g2t .' g2t
            LEFT JOIN '. $id2val .' id2val ON g2t.g = id2val.id
            GROUP BY g';

        // send SQL query
        $result = $this->store->queryDB($query, $this->store->getDBCon());

        $graphs = array();

        // collect graph URI's
        while ($row = $result->fetch_assoc()) {
            $graphs[$row['graphUri']] = $this->nodeFactory->createNamedNode($row['graphUri']);
        }

        return $graphs;
    }

    /**
     * Helper function to get the number of rows in a table.
     *
     * @param  string $tableName
     * @return int Number of rows in the target table.
     */
    protected function getRowCount($tableName)
    {
        $result = $this->store->queryDB(
            'SELECT COUNT(*) as count FROM '. $tableName,
            $this->store->getDBCon()
        );
        $row = $result->fetch_assoc();
        return $row['count'];
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
     * Creates and sets up an instance of ARC2_Store.
     */
    protected function openConnection()
    {
        // set standard values
        $this->configuration = array_merge(array(
            'host' => 'localhost',
            'database' => '',
            'username' => '',
            'password' => '',
            'table-prefix' => 'saft_'
        ), $this->configuration);

        // init store
        $this->store = \ARC2::getStore(array(
            'db_host' => $this->configuration['host'],
            'db_name' => $this->configuration['database'],
            'db_user' => $this->configuration['username'],
            'db_pwd' => $this->configuration['password'],
            'store_name' => $this->configuration['table-prefix']
        ));
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

        // execute query on the store
        $result = $this->store->query($query);

        if ('selectQuery' === AbstractQuery::getQueryType($query)) {
            /**
             * For a SELECT query the result looks like:
             *
             * array(
             *      'query_time' => 0.2
             *      'query_type' => 'select',
             *      'result' => array(
             *          'variables' => array('s', 'o')
             *          'rows' =>
             *              array(
             *                  's' => "http://s/"
             *                  's type' => 'uri',
             *                  'o' => '42',
             *                  'o type' => "literal"
             *                  'o datatype' => "http://www.w3.org/2001/XMLSchema#string"
             *              )
             */
            $entries = array();

            // go through all rows
            foreach ($result['result']['rows'] as $row) {
                $newEntry = array();

                foreach ($result['result']['variables'] as $variable) {
                    // checks for variable type
                    // example: $row['s type']
                    switch($row[$variable .' type']) {
                        // ARC2 does not differenciate between typed literal and literal, like Virtuoso does
                        // for instance. You have to check for lang and datatype key by yourself.
                        case 'literal':
                            // if language is set
                            if (isset($row[$variable .' lang'])) {
                                $newEntry[$variable] = $this->nodeFactory->createLiteral(
                                    $row[$variable],
                                    // set standard datatype if language tag is given
                                    'http://www.w3.org/1999/02/22-rdf-syntax-ns#langString',
                                    $row[$variable .' lang']
                                );

                            // if datatype is set
                            } elseif (isset($row[$variable .' datatype'])) {
                                $newEntry[$variable] = $this->nodeFactory->createLiteral(
                                    $row[$variable],
                                    $row[$variable .' datatype']
                                );

                            // if neither one is set, we assume its a string and use xsd:string as datatype
                            } else {
                                $newEntry[$variable] = $this->nodeFactory->createLiteral(
                                    $row[$variable],
                                    'http://www.w3.org/2001/XMLSchema#string'
                                );
                            }

                            break;

                        case 'uri':
                            $newEntry[$variable] = $this->nodeFactory->createNamedNode($row[$variable]);
                            break;
                    }
                }

                $entries[] = $newEntry;
            }

            // Create and fill SetResult instance
            $setResult = $this->resultFactory->createSetResult($entries);
            $setResult->setVariables($result['result']['variables']);
            return $setResult;

        } else {
            if ('askQuery' === AbstractQuery::getQueryType($query)) {
                return $this->resultFactory->createValueResult($result['result']);

            } else {
                return $this->resultFactory->createEmptyResult();
            }
        }
    }
}
