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

namespace Saft\Addition\ARC2\Store;

use Saft\Rdf\CommonNamespaces;
use Saft\Rdf\NamedNode;
use Saft\Rdf\Node;
use Saft\Rdf\NodeFactory;
use Saft\Rdf\RdfHelpers;
use Saft\Rdf\Statement;
use Saft\Rdf\StatementFactory;
use Saft\Rdf\StatementIterator;
use Saft\Rdf\StatementIteratorFactory;
use Saft\Sparql\Result\Result;
use Saft\Sparql\Result\ResultFactory;
use Saft\Sparql\Result\SetResult;
use Saft\Store\AbstractSparqlStore;

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
    protected $nodeFactory = null;

    /**
     * @var CommonNamespaces
     */
    protected $commonNamespaces = null;

    /**
     * @var RdfHelpers
     */
    protected $rdfHelpers = null;

    /**
     * @var QueryFactory
     */
    protected $queryFactory = null;

    /**
     * @var StatementFactory
     */
    protected $statementFactory = null;

    /**
     * @var StatementIteratorFactory
     */
    protected $statementIteratorFactory = null;

    /**
     * @var ARC2_Store
     */
    protected $store;

    /**
     * Constructor.
     *
     * @param NodeFactory              $nodeFactory
     * @param StatementFactory         $statementFactory
     * @param ResultFactory            $resultFactory
     * @param StatementIteratorFactory $statementIteratorFactory
     * @param RdfHelpers               $rdfHelpers
     * @param CommonNamespaces         $commonNamespaces
     * @param array                    $configuration            Array containing database credentials
     */
    public function __construct(
        NodeFactory $nodeFactory,
        StatementFactory $statementFactory,
        ResultFactory $resultFactory,
        StatementIteratorFactory $statementIteratorFactory,
        RdfHelpers $rdfHelpers,
        CommonNamespaces $commonNamespaces,
        array $configuration
    ) {
        $this->configuration = $configuration;
        $this->commonNamespaces = $commonNamespaces;

        // Open connection
        $this->openConnection();

        // Check if the store is setup. If not, create missing MySQL tables.
        if (!$this->store->isSetUp()) {
            $this->store->setUp();
        }

        $this->rdfHelpers = $rdfHelpers;

        $this->nodeFactory = $nodeFactory;
        $this->statementFactory = $statementFactory;
        $this->resultFactory = $resultFactory;
        $this->statementIteratorFactory = $statementIteratorFactory;

        parent::__construct(
            $nodeFactory,
            $statementFactory,
            $resultFactory,
            $statementIteratorFactory,
            $rdfHelpers
        );
    }

    /**
     * Adds multiple Statements to graph. It overrides parents addStatements because ARC2 only
     * supports SPARQL+ and not SPARQL Update 1.1, which means an INSERT INTO query has to look like:
     * INSERT INTO <http://graph/> { triple ... }.
     *
     * @param StatementIterator|array $statements statementList instance must contain Statement
     *                                            instances which are 'concret-' and not
     *                                            'pattern'-statements
     * @param Node                    $graph      optional Overrides target graph. If set, all statements
     *                                            will be add to that graph, if it is available.
     * @param array                   $options    optional Key-value pairs which provide additional
     *                                            introductions for the store and/or its
     *                                            adapter(s)
     *
     * @throws \Exception if one of the given Statements is not concrete
     */
    public function addStatements(iterable $statements, Node $graph = null, array $options = [])
    {
        $graphUriToUse = null;

        foreach ($statements as $statement) {
            if (!$statement->isConcrete()) {
                // non-concrete Statement instances not allowed
                // we have to undo the transaction somehow
                throw new \Exception('At least one Statement is not concrete');
            }

            if (null !== $graph) {
                // given $graph forces usage of it and not the graph from the statement instance
                $graphUriToUse = $graph->getUri();
            } elseif (null !== $statement->getGraph()) {
                // use graphUri from statement
                $graphUriToUse = $statement->getGraph()->getUri();
            }
            // else: non-concrete Statement instances not allowed

            // use full URIs, if available
            $s = $statement->getSubject();
            if ($s->isNamed()) {
                $s = $this->nodeFactory->createNamedNode(
                    $this->commonNamespaces->extendUri($statement->getSubject()->getUri())
                );
            }
            $p = $this->nodeFactory->createNamedNode(
                $this->commonNamespaces->extendUri($statement->getPredicate()->getUri())
            );
            $o = $statement->getObject();
            if ($o->isNamed()) {
                $o = $this->nodeFactory->createNamedNode(
                    $this->commonNamespaces->extendUri($statement->getObject()->getUri())
                );
            }

            $query = 'INSERT INTO <'.$graphUriToUse.'> {'
                .$s->toNQuads().' '
                .$p->toNQuads().' '
                .$o->toNQuads().' . '
                .'}';

            // execute query
            $res = $this->store->query($query, $options);
        }
    }

    /**
     * Closes current connection.
     */
    public function closeConnection()
    {
        $this->store->closeDBCon();
    }

    /**
     * Create a new graph with the URI given as NamedNode.
     *
     * @param NamedNode $graph   instance of NamedNode containing the URI of the graph to create
     * @param array     $options optional It contains key-value pairs and should provide additional
     *                           introductions for the store and/or its adapter(s)
     *
     * @throws \Exception if the given graph could not be created
     */
    public function createGraph(NamedNode $graph, array $options = [])
    {
        // ARC2 creates graph, if neccessary, no need to create them explicit.
    }

    /**
     * Removes all statements from a (default-) graph which match with given statement.
     *
     * @param Statement $statement it can be either a concrete or pattern-statement
     * @param Node      $graph     optional Overrides target graph. If set, all statements will
     *                             be delete in that graph.
     * @param array     $options   optional Key-value pairs which provide additional introductions
     *                             for the store and/or its adapter(s)
     */
    public function deleteMatchingStatements(
        Statement $statement,
        Node $graph = null,
        array $options = []
    ) {
        // given $graph forces usage of it and not the graph from the statement instance
        if (null !== $graph) {
            // use given $graph

        // use graphUri from statement
        } elseif (null === $graph && null !== $statement->getGraph()) {
            $graph = $statement->getGraph();
        }

        // use full URIs, if available
        $s = $statement->getSubject();
        if ($s->isNamed()) {
            $s = $this->nodeFactory->createNamedNode(
                $this->commonNamespaces->extendUri($statement->getSubject()->getUri())
            );
        }
        $p = $statement->getPredicate();
        if ($p->isNamed()) {
            $p = $this->nodeFactory->createNamedNode(
                $this->commonNamespaces->extendUri($statement->getPredicate()->getUri())
            );
        }
        $o = $statement->getObject();
        if ($o->isNamed()) {
            $o = $this->nodeFactory->createNamedNode(
                $this->commonNamespaces->extendUri($statement->getObject()->getUri())
            );
        }

        // create triple statement, because we have to handle the graph extra
        $tripleStatement = $this->statementFactory->createStatement($s, $p, $o);

        $statementIterator = $this->statementIteratorFactory->createStatementIteratorFromArray(
            [$tripleStatement]
        );

        $triple = $this->sparqlFormat($statementIterator);
        $query = 'DELETE ';
        if (null !== $graph) {
            $query .= 'FROM <'.$graph->getUri().'> ';
        }
        $query .= '{'.$triple.'} WHERE {'.$triple.'}';

        $this->query($query);
    }

    /**
     * Removes the given graph from the store.
     *
     * @param NamedNode $graph   instance of NamedNode containing the URI of the graph to drop
     * @param array     $options optional It contains key-value pairs and should provide additional
     *                           introductions for the store and/or its adapter(s)
     *
     * @throws \Exception If the given graph could not be droped
     */
    public function dropGraph(NamedNode $graph, array $options = [])
    {
        $this->store->query('DELETE FROM <'.$graph->getUri().'>');
    }

    /**
     * Empties all ARC2-related tables from the database.
     */
    public function emptyAllTables()
    {
        $this->store->reset();
    }

    /**
     * Returns the currently active connection to the database.
     */
    public function getConnection()
    {
        return $this->store->getDBCon();
    }

    /**
     * Returns a list of all available graph URIs of the store. It can also respect access control,
     * to only returned available graphs in the current context. But that depends on the implementation
     * and can differ.
     *
     * @return iterable simple array of key-value-pairs, which consists of graph URIs as key and NamedNode
     *                  instance as value
     */
    public function getGraphs(): iterable
    {
        throw new \Exception(
            'Not implemented, because ARC2 creates graphs on demand. Empty graphs are not supported in ARC2.'
        );
    }

    /**
     * Helper function to get the number of rows in a table.
     *
     * @param string $tableName
     *
     * @return int number of rows in the target table
     */
    public function getRowCount($tableName): int
    {
        $result = $this->store->queryDB(
            'SELECT COUNT(*) as count FROM '.$tableName,
            $this->store->getDBCon()
        );
        $row = $result->fetch_assoc();

        return $row['count'];
    }

    /**
     * Allows access of the ARC2 instance.
     *
     * @return ARC2_Store
     */
    public function getStore(): \ARC2_Store
    {
        return $this->store;
    }

    /**
     * Creates and sets up an instance of ARC2_Store.
     */
    protected function openConnection()
    {
        // set standard values
        $this->configuration = array_merge([
            'db_host' => 'localhost',
            'db_name' => '',
            'db_user' => '',
            'db_pwd' => '',
            'store_name' => '',
            'db_table_prefix' => 'saft_',
            'db_adapter' => 'pdo',
            'db_pdo_protocol' => 'mysql',
            'cache_enabled' => true
        ], $this->configuration);

        /*
         * check for missing connection credentials
         */
        if ('' == $this->configuration['db_name']) {
            throw new \Exception('ARC2: Field db_name is not set.');
        } elseif ('' == $this->configuration['db_user']) {
            throw new \Exception('ARC2: Field db_user is not set.');
        } elseif ('' == $this->configuration['db_host']) {
            throw new \Exception('ARC2: Field db_host is not set.');
        }

        // init store
        $this->store = \ARC2::getStore($this->configuration);
        $this->store->createDBCon();
        if (0 < \count($this->store->errors)) {
            throw new \Exception('Error(s) when creating new connection: '.\implode(', ', $this->store->errors));
        }
        $this->store->setup();
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
     * @throws \Exception if query is no string
     * @throws \Exception if query is malformed
     * @throws \Exception If query is a DELETE query and contains quads, where the graph of one quad is of type var
     * @throws \Exception if a non-graph query contains no triples and quads
     *
     * @todo handle multiple graphs in FROM clause
     */
    public function query(string $query, array $options = []): Result
    {
        $queryType = $this->getQueryType($query);

        // rewrite CLEAR GRAPH query to DELETE DATA <...>
        if (1 == \preg_match('/CLEAR GRAPH <(.*?)>/i', $query, $match)) {
            $query = 'DELETE FROM <'.$match[1].'>';
            $result = $this->store->query($query);
            return $this->resultFactory->createEmptyResult();
        }

        // execute query on the store
        $result = $this->store->query($query);

        /*
         * special case: if you execute a SELECT COUNT(*) query, ARC2 will return the number of triples
                         instead of a result set
         */
        $countCheck = \preg_match(
            '/selectcount\([a-z*]\)(from|where)/si',
            \preg_replace('/\s+/', '', $query) // remove all whitespaces
        );
        if (1 == $countCheck) {
            $variable = 'callret-0';
            // build a set result, because the user expects it as result type because a SELECT query
            // was sent.
            $setResult = $this->resultFactory->createSetResult(
                [
                    [
                        $variable => $this->nodeFactory->createLiteral(
                            $result,
                            'http://www.w3.org/2001/XMLSchema#int'
                        ),
                    ],
                ]
            );
            $setResult->setVariables([$variable]);

            return $setResult;

        /*
         * CONSTRUCT query
         */
        } elseif ('construct' === $queryType) {
            $statements = [];
            foreach ($result['result'] as $subjectUri => $predicates) {
                foreach ($predicates as $predicateUri => $objects) {
                    foreach ($objects as $objectArray) {
                        // is subject blank node or not
                        if ('_:' == substr($subjectUri, 0, 2)) {
                            $subjectNode = $this->nodeFactory->createBlankNode($subjectUri);
                        } else {
                            $subjectNode = $this->nodeFactory->createNamedNode($subjectUri);
                        }

                        $statements[] = $this->statementFactory->createStatement(
                            $subjectNode,
                            $this->nodeFactory->createNamedNode($predicateUri),
                            $this->transformEntryToNode($objectArray)
                        );
                    }
                }
            }

            if (0 == count($statements)) {
                return $this->resultFactory->createEmptyResult();
            } else {
                return $this->resultFactory->createStatementResult($statements);
            }

         /*
          * SELECT query
          */
         } else {
            /*
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
            $entries = [];

            // go through all rows
            if (isset($result['result']['rows'])) {
                foreach ($result['result']['rows'] as $row) {
                    $newEntry = [];

                    foreach ($result['result']['variables'] as $variable) {
                        // checks for variable type
                        // example: $row['s type']
                        switch ($row[$variable.' type']) {
                            // ARC2 does not differenciate between typed literal and literal, like Virtuoso does
                            // for instance. You have to check for lang and datatype key by yourself.
                            case 'literal':
                                // if language is set
                                if (isset($row[$variable.' lang'])) {
                                    $newEntry[$variable] = $this->nodeFactory->createLiteral(
                                        $row[$variable],
                                        // set standard datatype if language tag is given
                                        'http://www.w3.org/1999/02/22-rdf-syntax-ns#langString',
                                        $row[$variable.' lang']
                                    );

                                // if datatype is set
                                } elseif (isset($row[$variable.' datatype'])) {
                                    $newEntry[$variable] = $this->nodeFactory->createLiteral(
                                        $row[$variable],
                                        $row[$variable.' datatype']
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
                                // ARC2 seems to think that an email is a valid URI.
                                if ($this->rdfHelpers->simpleCheckURI($row[$variable])) {
                                    $newEntry[$variable] = $this->nodeFactory->createNamedNode($row[$variable]);
                                // we force such things as literal
                                } else {
                                    $newEntry[$variable] = $this->nodeFactory->createLiteral(
                                        $row[$variable],
                                        'http://www.w3.org/2001/XMLSchema#string'
                                    );
                                }
                                break;

                            case 'bnode':
                                $newEntry[$variable] = $this->nodeFactory->createBlankNode(
                                    substr($row[$variable], 2) // use ID without _:
                                );
                                break;
                        }
                    }

                    $entries[] = $newEntry;
                }
            }

            // Create and fill SetResult instance
            $setResult = $this->resultFactory->createSetResult($entries);
            $setResult->setVariables($result['result']['variables']);

            return $setResult;
        }
    }
}
