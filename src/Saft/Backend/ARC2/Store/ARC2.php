<?php

namespace Saft\Backend\ARC2\Store;

use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\Node;
use Saft\Rdf\NodeFactory;
use Saft\Rdf\StatementFactory;
use Saft\Rdf\StatementIterator;
use Saft\Sparql\Query\AbstractQuery;
use Saft\Store\AbstractSparqlStore;
use Saft\Store\Exception\StoreException;
use Saft\Store\Result\EmptyResult;
use Saft\Store\Result\SetResult;

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
     * @var StatementFactory
     */
    private $statementFactory = null;

    /**
     * @var ARC2_Store
     */
    protected $store;

    /**
     * Constructor.
     *
     * @param  array $configuration Array containing database credentials
     */
    public function __construct(NodeFactory $nodeFactory, StatementFactory $statementFactory, array $configuration)
    {
        $this->configuration = $configuration;

        // Open connection
        $this->openConnection();

        // Check if the store is setup. If not, create missing MySQL tables.
        if (!$this->store->isSetUp()) {
            $this->store->setUp();
        }

        $this->nodeFactory = $nodeFactory;
        $this->statementFactory = $statementFactory;

        parent::__construct($nodeFactory, $statementFactory);
    }

    /**
     * Adds multiple Statements to (default-) graph. It overrides parents addStatements because ARC2 only
     * supports SPARQL+ and not SPARQL Update 1.1, which means an INSERT INTO query has to look like:
     * INSERT INTO <http://graph/> { triple ... }.
     *
     * @param  StatementIterator $statements          StatementList instance must contain Statement instances
     *                                                which are 'concret-' and not 'pattern'-statements.
     * @param  Node              $graph      optional Overrides target graph. If set, all statements will
     *                                                be add to that graph, if available.
     * @param  array             $options    optional It contains key-value pairs and should provide additional
     *                                                introductions for the store and/or its adapter(s).
     */
    public function addStatements(StatementIterator $statements, Node $graph = null, array $options = array())
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
            } else {
                throw new \Exception('Graph was not given, neither as parameter nor in statement.');
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
                 * $batchStatements is an array with graphUri('s) as key(s) and ArrayStatementIteratorImpl
                 * instances as value. Each entry is related to a certain graph and contains a bunch of
                 * statement instances.
                 */
                foreach ($batchStatements as $graphUriToUse => $statementBatch) {
                    $this->query(
                        'INSERT INTO <'. $graphUriToUse .'> {'.
                        $this->sparqlFormat(new ArrayStatementIteratorImpl($statementBatch)) .
                        '}',
                        $options
                    );
                }

                // re-init variables
                $batchStatements = array();
            }
        }
    }

    /**
     * Creates and sets up an instance of ARC2_Store.
     */
    public function openConnection()
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
     * @return array Empty array
     * @todo implement getStoreDescription
     */
    public function getStoreDescription()
    {
        return array();
    }

    /**
     * This method sends a SPARQL query to the store.
     *
     * @param  string $query            The SPARQL query to send to the store.
     * @param  array  $options optional It contains key-value pairs and should provide additional introductions
     *                                  for the store and/or its adapter(s).
     * @return Result Returns result of the query. Depending on the query type, it returns either an instance
     *                of EmptyResult, SetResult, StatementResult or ValueResult.
     * @throws \Exception If query is no string.
     * @throws \Exception If query is malformed.
     * @todo handle multiple graphs in FROM clause
     */
    public function query($query, array $options = array())
    {
        $queryObject = AbstractQuery::initByQueryString($query);
        $queryParts = $queryObject->getQueryParts();

        // if a non-graph query was given, we assume triples or quads. If neither quads nor triples were found,
        // throw an exception.
        if (false === $queryObject->isGraphQuery()
            && false === isset($queryParts['triple_pattern'])
            && false === isset($queryParts['quad_pattern'])) {
            throw new StoreException('Non-graph queries must have triples or quads.');
        }

        // execute query on the store
        $result = $this->store->query($query);
        $finalResult = null;

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
            $finalResult = new SetResult();
            $finalResult->setVariables($result['result']['variables']);

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

                    $finalResult->append($newEntry);
                }
            }
        }

        return $finalResult;
    }
}
