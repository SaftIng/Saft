<?php

namespace Saft\Store\Test;

use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\LiteralImpl;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\StatementImpl;
use Saft\Rdf\StatementIterator;
use Saft\Rdf\AnyPatternImpl;
use Saft\Sparql\SparqlUtils;
use Saft\Store\Result\EmptyResult;
use Saft\Store\Result\EmptyResultImpl;
use Saft\Store\Result\StatementSetResultImpl;
use Saft\Store\Result\SetResultImpl;
use Saft\Store\Result\ValueResultImpl;
use Saft\Test\TestCase;
use Symfony\Component\Yaml\Parser;

abstract class StoreAbstractTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        if (null !== $this->fixture) {
            $this->fixture->dropGraph($this->testGraph);
            $this->fixture->createGraph($this->testGraph);
        }
    }

    /**
     *
     */
    public function tearDown()
    {
        if (null !== $this->fixture) {
            $this->fixture->dropGraph($this->testGraph);
        }

        parent::tearDown();
    }

    protected function getTestQuad()
    {
        $subject1 = new NamedNodeImpl('http://saft/testquad/s1');
        $predicate1 = new NamedNodeImpl('http://saft/testquad/p1');
        $object1 = new NamedNodeImpl('http://saft/testquad/o1');
        $graph1 = new NamedNodeImpl('http://saft/testquad/g1');
        $quad = new StatementImpl($subject1, $predicate1, $object1, $graph1);

        return new StatementImpl($subject1, $predicate1, $object1, $graph1);
    }

    protected function getTestTriple()
    {
        $subject2 = new NamedNodeImpl('http://saft/testtriple/s2');
        $predicate2 = new NamedNodeImpl('http://saft/testtriple/p2');
        $object2 = new NamedNodeImpl('http://saft/testtriple/o2');
        $triple = new StatementImpl($subject2, $predicate2, $object2);

        return new StatementImpl($subject2, $predicate2, $object2);
    }

    protected function getTestPatternStatement()
    {
        $subject1 = new AnyPatternImpl();
        $predicate1 = new AnyPatternImpl();
        $object1 = new AnyPatternImpl();

        return new StatementImpl($subject1, $predicate1, $object1);
    }

    protected function getTestStatementWithLiteral()
    {
        $subject2 = new NamedNodeImpl('http://saft/test/s1');
        $predicate2 = new NamedNodeImpl('http://saft/test/p2');
        $object2 = new LiteralImpl('John');
        return new StatementImpl($subject2, $predicate2, $object2, $this->testGraph);
    }

    /*
     * Actual test methods
     */

    public function testAddStatements()
    {
        $anyStatement = new StatementImpl(
            new AnyPatternImpl(),
            new AnyPatternImpl(),
            new AnyPatternImpl(),
            $this->testGraph
        );

        // graph is empty
        $statements = $this->fixture->getMatchingStatements($anyStatement, $this->testGraph);
        $this->assertCountStatementIterator(0, $statements);

        // 2 triples
        $statements = new ArrayStatementIteratorImpl(array(
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new NamedNodeImpl('http://o/')
            ),
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new LiteralImpl('test literal')
            ),
        ));

        // add triples
        $this->fixture->addStatements($statements, $this->testGraph);

        // graph has two entries
        $statements = $this->fixture->getMatchingStatements($anyStatement, $this->testGraph);
        $this->assertCountStatementIterator(2, $statements);
    }


    public function testAddAndDeleteStatementsOnDefaultGraph()
    {
        $stmtOne = new StatementImpl(
            new NamedNodeImpl('http://s/'),
            new NamedNodeImpl('http://p/'),
            new NamedNodeImpl('http://o/')
        );
        $stmtTwo = new StatementImpl(
            new NamedNodeImpl('http://s/'),
            new NamedNodeImpl('http://p/'),
            new LiteralImpl('test literal')
        );

        if ($this->fixture->hasMatchingStatement($stmtOne) || $this->fixture->hasMatchingStatement($stmtTwo)) {
            $this->markTestSkipped("Skip this test, because one of our test triples already exists");
        }

        // 2 triples
        $statements = new ArrayStatementIteratorImpl([$stmtOne, $stmtTwo]);

        // add triples
        $this->fixture->addStatements($statements);

        // graph has the two entries
        $this->assertTrue($this->fixture->hasMatchingStatement($stmtOne));
        $this->assertTrue($this->fixture->hasMatchingStatement($stmtTwo));

        $this->fixture->deleteMatchingStatements($stmtOne);
        $this->fixture->deleteMatchingStatements($stmtTwo);

        // graph has the two entries
        $this->assertFalse($this->fixture->hasMatchingStatement($stmtOne));
        $this->assertFalse($this->fixture->hasMatchingStatement($stmtTwo));
    }

    public function testAddStatementsWithArray()
    {
        $anyStatement = new StatementImpl(
            new AnyPatternImpl(),
            new AnyPatternImpl(),
            new AnyPatternImpl(),
            $this->testGraph
        );

        // graph is empty
        $statements = $this->fixture->getMatchingStatements($anyStatement, $this->testGraph);
        $this->assertCountStatementIterator(0, $statements);

        // 2 triples
        $statements = array(
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new NamedNodeImpl('http://o/')
            ),
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new LiteralImpl('test literal')
            ),
        );

        // add triples
        $this->fixture->addStatements($statements, $this->testGraph);

        // graph has two entries
        $statements = $this->fixture->getMatchingStatements($anyStatement, $this->testGraph);
        $this->assertCountStatementIterator(2, $statements);
    }

    public function testAddStatementsInvalidStatements()
    {
        // build statement iterator containing one statement which consists only of variables.
        $statements = new ArrayStatementIteratorImpl(array(
            new StatementImpl(new AnyPatternImpl(), new AnyPatternImpl(), new AnyPatternImpl())
        ));

        // expect exception, because only concrete (no variable) statements are allowed
        $this->setExpectedException('\Exception');
        $this->fixture->addStatements($statements);
    }

    public function testAddStatementsLanguageTags()
    {
        $anyStatement = new StatementImpl(
            new AnyPatternImpl(),
            new AnyPatternImpl(),
            new AnyPatternImpl(),
            $this->testGraph
        );

        // graph is empty
        $statements = $this->fixture->getMatchingStatements($anyStatement, $this->testGraph);
        $this->assertCountStatementIterator(0, $statements);

        // 2 triples
        $statements = new ArrayStatementIteratorImpl(array(
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new LiteralImpl('test literal', null, 'en')
            ),
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new LiteralImpl('test literal', null, 'de')
            ),
        ));

        // add triples
        $this->fixture->addStatements($statements, $this->testGraph);

        // graph has now two entries
        $statements = $this->fixture->getMatchingStatements($anyStatement, $this->testGraph);
        $this->assertCountStatementIterator(2, $statements);
    }

    public function testAddStatementsUseStatementGraph()
    {
        // remove all triples from the test graph
        $this->fixture->query('CLEAR GRAPH <' . $this->testGraph->getUri() . '>');

        // graph is empty
        $anyStatement = new StatementImpl(
            new AnyPatternImpl(),
            new AnyPatternImpl(),
            new AnyPatternImpl(),
            $this->testGraph
        );
        $statements = $this->fixture->getMatchingStatements($anyStatement, $this->testGraph);
        $this->assertCountStatementIterator(0, $statements);

        // 2 triples
        $statements = new ArrayStatementIteratorImpl(array(
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new NamedNodeImpl('http://o/'),
                $this->testGraph
            ),
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new LiteralImpl('test literal'),
                $this->testGraph
            ),
        ));

        // add triples
        $this->fixture->addStatements($statements);

        // graph has two entries
        $statements = $this->fixture->getMatchingStatements($anyStatement, $this->testGraph);
        $this->assertCountStatementIterator(2, $statements);
    }

    public function testAddStatementsNoTriplesAndQuads()
    {
        // it throws an error because query contains NO triples or quads.
        $this->setExpectedException('\Exception');

        $query = 'INSERT DATA {  }';
        $this->fixture->query($query);
    }

    public function testAddStatementsTriples()
    {
        $statement = $this->getTestStatementWithLiteral();
        $statementIterator = new ArrayStatementIteratorImpl(array($statement));
        $query = 'INSERT DATA {
            Graph <http://graph/> {
                '. $statement->getSubject()->toNQuads() .'
                '. $statement->getPredicate()->toNQuads() .'
                '. $statement->getObject()->toNQuads() .'
            }
        }';

        $this->assertEquals(new EmptyResultImpl(), $this->fixture->query($query));
    }

    /*
     * Tests createGraph
     */

    public function testCreateGraph()
    {
        $this->fixture->createGraph($this->testGraph);

        // TODO try to add Triples
    }

    /*
     * function dropGraph
     *
     * We can drop the graph and create a graph, but we can't asume any action since a store might
     * not support empty graphs.
     */

    public function testDropGraph()
    {
        $this->fixture->dropGraph($this->testGraph);

        $this->fixture->createGraph($this->testGraph);

        // TODO add Triples to the graph but expect Exception

        $this->fixture->dropGraph($this->testGraph);
    }

    /*
     * Tests deleteMatchingStatements
     */

    public function testDeleteMatchingStatements2()
    {
        /**
         * Create some test data
         */
        $anyStatement = new StatementImpl(
            new AnyPatternImpl(),
            new AnyPatternImpl(),
            new AnyPatternImpl(),
            $this->testGraph
        );
        $statements = $this->fixture->getMatchingStatements($anyStatement, $this->testGraph);
        $this->assertCountStatementIterator(0, $statements);

        // 2 triples
        $statements = new ArrayStatementIteratorImpl(array(
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new NamedNodeImpl('http://o/')
            ),
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new LiteralImpl('test literal')
            ),
        ));

        // add triples
        $this->fixture->addStatements($statements, $this->testGraph);

        // count two triples
        $statements = $this->fixture->getMatchingStatements($anyStatement, $this->testGraph);
        $this->assertCountStatementIterator(2, $statements);

        /**
         * drop all triples
         */
        $this->fixture->deleteMatchingStatements(
            new StatementImpl(new NamedNodeImpl('http://s/'), new NamedNodeImpl('http://p/'), new AnyPatternImpl()),
            $this->testGraph
        );

        // count no triples
        $statements = $this->fixture->getMatchingStatements($anyStatement, $this->testGraph);
        $this->assertCountStatementIterator(0, $statements);
    }

    public function testDeleteMatchingStatementsNoGraphGiven()
    {
        // expect exception thrown, because no graph was given, neither set in Statement nor given extra
        $this->setExpectedException('\Exception');

        $this->fixture->deleteMatchingStatements(
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new AnyPatternImpl()
            )
        );
    }

    public function testDeleteMatchingStatementsUseStatementGraph()
    {
        /**
         * Create some test data
         */
        $anyStatement = new StatementImpl(
            new AnyPatternImpl(),
            new AnyPatternImpl(),
            new AnyPatternImpl(),
            $this->testGraph
        );
        $statements = $this->fixture->getMatchingStatements($anyStatement);
        $this->assertCountStatementIterator(0, $statements);

        // 2 triples
        $statements = new ArrayStatementIteratorImpl(array(
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new NamedNodeImpl('http://o/'),
                $this->testGraph
            ),
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new LiteralImpl('test literal'),
                $this->testGraph
            ),
        ));

        // add triples
        $this->fixture->addStatements($statements, $this->testGraph);

        // count two triples
        $statements = $this->fixture->getMatchingStatements($anyStatement);
        $this->assertCountStatementIterator(2, $statements);

        /**
         * drop all triples
         */
        $this->fixture->deleteMatchingStatements(
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new AnyPatternImpl(),
                $this->testGraph
            )
        );

        // count no triples
        $statements = $this->fixture->getMatchingStatements($anyStatement, $this->testGraph);
        $this->assertCountStatementIterator(0, $statements);
    }

    public function testDeleteMatchingStatementsWithVariables()
    {
        /**
         * Create some test data
         */
        $anyStatement = new StatementImpl(
            new AnyPatternImpl(),
            new AnyPatternImpl(),
            new AnyPatternImpl(),
            $this->testGraph
        );
        $statements = $this->fixture->getMatchingStatements($anyStatement, $this->testGraph);
        $this->assertCountStatementIterator(0, $statements);

        // 2 triples
        $statements = new ArrayStatementIteratorImpl(array(
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new NamedNodeImpl('http://o/')
            ),
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new LiteralImpl('test literal')
            ),
        ));

        // add triples
        $this->fixture->addStatements($statements, $this->testGraph);

        // count two triples
        $statements = $this->fixture->getMatchingStatements($anyStatement, $this->testGraph);
        $this->assertCountStatementIterator(2, $statements);

        /**
         * drop all triples
         */
        $this->fixture->deleteMatchingStatements(
            new StatementImpl(new AnyPatternImpl(), new AnyPatternImpl(), new AnyPatternImpl()),
            $this->testGraph
        );

        // count no triples
        $statements = $this->fixture->getMatchingStatements($anyStatement, $this->testGraph);
        $this->assertCountStatementIterator(0, $statements);
    }

    /*
     * Tests deleteMultipleStatements
     */

    public function testDeleteMultipleStatementsQuadRecognition()
    {
        $quad = $this->getTestQuad();
        $graphPattern = SparqlUtils::statementsToSparqlFormat([$quad]);
        $query = 'DELETE DATA { ' . $graphPattern . '}';
        $queryResult = $this->fixture->query($query);

        $this->assertClassOfInstanceImplements($queryResult, 'Saft\Store\Result\Result');
        $this->assertTrue($queryResult->isEmptyResult());
    }

    public function testDeleteMultipleStatementsVariablePatterns()
    {
        $this->markTestSkipped("TODO implement test store which expects certain things on query");
        $statement = $this->getTestPatternStatement();
        $query = 'DELETE DATA { '. SparqlUtils::statementsToSparqlFormat([$statement]) .'}';

        $this->assertEquals(
            new EmptyResult(),
            $this->fixture->query($query)
        );
    }

    public function testDeleteMultipleStatementsStatementsWithLiteral()
    {
        $statement = $this->getTestStatementWithLiteral();

        $query = 'DELETE DATA { '. SparqlUtils::statementsToSparqlFormat([$statement]) .'}';

        $this->assertEquals(
            new EmptyResultImpl(),
            $this->fixture->query($query)
        );
    }

    /**
     * test for method getAvailableGraphs
     */

    public function testGetAvailableGraphs()
    {
        $this->fixture->createGraph($this->testGraph);

        // FYI: $availableGraphs is an array containing graph URI strings as key and a respective NamedNode as value.
        $availableGraphs = $this->fixture->getAvailableGraphs();

        $graphUri = $this->testGraph->getUri();

        // check, that our test graph is part of the array
        $this->assertTrue(isset($availableGraphs[$graphUri]), "The test graph is not available");
        $this->assertTrue($this->testGraph->equals($availableGraphs[$graphUri]), "The test graph object is not valid");
    }

    /*
     * Tests getMatchingStatements
     */

    public function testGetMatchingStatementsReturnType()
    {
        $statement = new StatementImpl(
            new NamedNodeImpl('http://s/'),
            new NamedNodeImpl('http://p/'),
            new AnyPatternImpl()
        );

        $iterator = $this->fixture->getMatchingStatements($statement);

        $this->assertTrue(
            $iterator instanceof StatementIterator,
            "Get Matching Statements has to return a StatementIterator"
        );
    }

    public function testGetMatchingStatements()
    {
        // 2 triples
        $statements = new ArrayStatementIteratorImpl(array(
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new NamedNodeImpl('http://o/')
            ),
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new LiteralImpl('test literal')
            ),
        ));

        // add triples
        $this->fixture->addStatements($statements, $this->testGraph);

        $statement = new StatementImpl(
            new NamedNodeImpl('http://s/'),
            new NamedNodeImpl('http://p/'),
            new AnyPatternImpl()
        );

        /**
         * Build StatementIterator instance to check against
         */
        $instanceToCheckAgainst = new ArrayStatementIteratorImpl(array(
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new NamedNodeImpl('http://o/'),
                $this->testGraph
            ),
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new LiteralImpl('test literal'),
                $this->testGraph
            )
        ));

        $this->assertEquals(
            $instanceToCheckAgainst,
            $this->fixture->getMatchingStatements($statement, $this->testGraph)
        );
    }

    public function testGetMatchingStatementsEmptyGraph()
    {
        $statement = new StatementImpl(new AnyPatternImpl(), new AnyPatternImpl(), new AnyPatternImpl());

        $this->assertEquals(
            new ArrayStatementIteratorImpl(array()),
            $this->fixture->getMatchingStatements($statement, $this->testGraph)
        );
    }

    public function testGetMatchingStatementsCheckGraph()
    {
        // 2 triples
        $statements = new ArrayStatementIteratorImpl(array(
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new NamedNodeImpl('http://o/')
            ),
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new LiteralImpl('test literal')
            ),
        ));

        // add triples
        $this->fixture->addStatements($statements, $this->testGraph);

        $statement = new StatementImpl(
            new NamedNodeImpl('http://s/'),
            new NamedNodeImpl('http://p/'),
            new AnyPatternImpl(),
            $this->testGraph
        );

        $iterator = $this->fixture->getMatchingStatements($statement);

        foreach ($iterator as $statement) {
            // this check is to avoid fatal error in test suite, because graph is null
            $this->assertTrue(null !== $statement->getGraph());

            $this->assertEquals($statement->getGraph()->getUri(), $this->testGraph->getUri());
        }
    }

    public function testGetMatchingStatementsCheckForEmptyGraph()
    {
        // 2 triples
        $statements = new ArrayStatementIteratorImpl(array(
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new NamedNodeImpl('http://o/')
            ),
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new LiteralImpl('test literal')
            ),
        ));

        // add triples
        $this->fixture->addStatements($statements, $this->testGraph);

        $statement = new StatementImpl(
            new NamedNodeImpl('http://s/'),
            new NamedNodeImpl('http://p/'),
            new AnyPatternImpl()
        );

        $iterator = $this->fixture->getMatchingStatements($statement);

        foreach ($iterator as $statement) {
            $this->assertNull($statement->getGraph());
        }
    }

    /**
     * Tests getStoreDescription
     */

    // Test if an array for the store description is returned
    public function testGetStoreDescription()
    {
        $this->assertTrue(is_array($this->fixture->getStoreDescription()));
    }

    /*
     * Tests hasMatchingStatements
     */

    public function testHasMatchingStatement()
    {
        // 2 triples
        $statements = new ArrayStatementIteratorImpl(array(
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new NamedNodeImpl('http://o/')
            ),
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new LiteralImpl('test literal')
            ),
        ));

        // add triples
        $this->fixture->addStatements($statements, $this->testGraph);

        $statement = new StatementImpl(new AnyPatternImpl(), new AnyPatternImpl(), new AnyPatternImpl());

        $this->assertTrue($this->fixture->hasMatchingStatement($statement, $this->testGraph));
    }

    public function testHasMatchingStatementEmptyGraph()
    {
        $this->fixture->query('CLEAR GRAPH <'. $this->testGraph->getUri() .'>');

        $statement = new StatementImpl(new AnyPatternImpl(), new AnyPatternImpl(), new AnyPatternImpl());

        $this->assertFalse($this->fixture->hasMatchingStatement($statement, $this->testGraph));
    }

    public function testHasMatchingStatementOnlyVariables()
    {
        $this->fixture->query('CLEAR GRAPH <'. $this->testGraph->getUri() .'>');

        $statement = new StatementImpl(
            new AnyPatternImpl(),
            new AnyPatternImpl(),
            new AnyPatternImpl(),
            $this->testGraph
        );

        $this->assertFalse($this->fixture->hasMatchingStatement($statement));
    }

    // triple recognition
    public function testHasMatchingStatementTripleRecognition()
    {
        $triple = $this->getTestTriple();
        $query = 'ASK { '. SparqlUtils::statementsToSparqlFormat([$triple]) .'}';

        $this->assertEquals(
            new ValueResultImpl(false),
            $this->fixture->query($query)
        );
    }

    /*
     * Tests query
     */

    public function testQuery()
    {
        $this->fixture->query('CLEAR GRAPH <'. $this->testGraph->getUri() .'>');

        // 2 triples
        $statements = new ArrayStatementIteratorImpl(array(
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new NamedNodeImpl('http://o/')
            ),
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new LiteralImpl(
                    'foobar',
                    new NamedNodeImpl('http://www.w3.org/1999/02/22-rdf-syntax-ns#langString'),
                    'en'
                )
            ),
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new LiteralImpl(42)
            ),
        ));

        // add triples
        $this->fixture->addStatements($statements, $this->testGraph);

        /**
         * Build SetResult instance to check against
         */
        $setResultToCheckAgainst = new SetResultImpl(
            new \ArrayIterator(
                array(
                    array(
                        's' => new NamedNodeImpl('http://s/'),
                        'o' =>
                        new LiteralImpl(
                            'foobar',
                            new NamedNodeImpl('http://www.w3.org/1999/02/22-rdf-syntax-ns#langString'),
                            'en'
                        )
                    ),
                    array(
                        's' => new NamedNodeImpl('http://s/'),
                        'o' => new LiteralImpl('42')
                    ),
                    array(
                        's' => new NamedNodeImpl('http://s/'),
                        'o' => new NamedNodeImpl('http://o/')
                    )
                )
            )
        );
        $setResultToCheckAgainst->setVariables(array('s', 'o'));

        // check
        $this->assertIteratorContent(
            $setResultToCheckAgainst,
            $this->fixture->query(
                'SELECT ?s ?o FROM <' . $this->testGraph->getUri() . '> WHERE {?s ?p ?o.} ORDER BY ?o'
            )
        );
    }

    public function testQueryAsk()
    {
        $this->fixture->query('CLEAR GRAPH <'. $this->testGraph->getUri() .'>');

        $anyStatement = new StatementImpl(
            new AnyPatternImpl(),
            new AnyPatternImpl(),
            new AnyPatternImpl(),
            $this->testGraph
        );

        // graph is empty
        $statements = $this->fixture->getMatchingStatements($anyStatement, $this->testGraph);
        $this->assertCountStatementIterator(0, $statements);

        // 2 triples
        $statements = new ArrayStatementIteratorImpl(array(
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new NamedNodeImpl('http://o/')
            ),
        ));

        // add triples
        $this->fixture->addStatements($statements, $this->testGraph);

        $this->assertEquals(
            new ValueResultImpl(true),
            $this->fixture->query(
                'ASK FROM <'. $this->testGraph->getUri() . '> {<http://s/> <http://p/> ?o.}'
            )
        );
    }

    public function testQueryEmptyResult()
    {
        $setResult = new SetResultImpl(new \ArrayIterator());
        $setResult->setVariables(array('s', 'p', 'o'));

        $this->assertEquals(
            $setResult,
            $this->fixture->query('SELECT ?s ?p ?o FROM <'. $this->testGraph->getUri() .'> WHERE {?s ?p ?o.}')
        );
    }

    public function testQueryDeleteMultipleStatementsQuadRecognition()
    {
        $quad = $this->getTestQuad();
        $graphPattern = SparqlUtils::statementsToSparqlFormat([$quad]);
        $query = 'DELETE DATA {' . $graphPattern . '}';

        $this->assertEquals(new EmptyResultImpl(), $this->fixture->query($query));
    }

    public function testQueryDeleteMultipleStatementsVariablePatterns()
    {
        $this->markTestSkipped("TODO implement test store which expects certain things on query");
        $statement = $this->getTestPatternStatement();
        $query = 'DELETE DATA { '. SparqlUtils::statementsToSparqlFormat([$statement]) .'}';

        $this->assertEquals(
            new EmptyResult(),
            $this->fixture->query($query)
        );
    }

    public function testQueryDeleteMultipleStatementsStatementsWithLiteral()
    {
        $statement = $this->getTestStatementWithLiteral();

        $query = 'DELETE DATA { '. SparqlUtils::statementsToSparqlFormat([$statement]) .'}';

        $this->assertEquals(
            new EmptyResultImpl(),
            $this->fixture->query($query)
        );
    }
}
