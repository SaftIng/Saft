<?php

namespace Saft\Store\Test;

use Saft\Rdf\AnyPatternImpl;
use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\LiteralImpl;
use Saft\Rdf\NamedNode;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\RdfHelpers;
use Saft\Rdf\StatementImpl;
use Saft\Rdf\StatementIterator;
use Saft\Rdf\StatementIteratorFactoryImpl;
use Saft\Sparql\Result\EmptyResultImpl;
use Saft\Sparql\Result\ResultFactoryImpl;
use Saft\Sparql\Result\SetResultImpl;
use Saft\Sparql\Result\StatementSetResultImpl;
use Saft\Sparql\Result\ValueResultImpl;
use Saft\Test\TestCase;
use Symfony\Component\Yaml\Parser;

/**
 * @codeCoverageIgnore
 */
abstract class StoreAbstractTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function tearDown()
    {
        if (null !== $this->fixture) {
            $this->fixture->dropGraph($this->testGraph);
        }

        parent::tearDown();
    }

    /*
     * Helper functions
     */

    /**
     * Counts all triples of a given graph using SELECT COUNT.
     *
     * @param NamedNode $graph NamedNode instance which represents the graph to count its triple.
     * @return int Number of triples in the graph.
     */
    protected function countTriples(NamedNode $graph)
    {
        $result = $this->fixture->query(
            'SELECT COUNT(*) FROM <'. $graph->getUri() .'> WHERE {?s ?p ?o}'
        );

        $variables = $result->getVariables();
        $variable = array_shift($variables);
        $entry = $result->current();
        $keys = array_keys($entry);
        return $entry[array_shift($keys)]->getValue();
    }

    protected function getTestQuad()
    {
        $subject1 = new NamedNodeImpl(new RdfHelpers(), 'http://saft/testquad/s1');
        $predicate1 = new NamedNodeImpl(new RdfHelpers(), 'http://saft/testquad/p1');
        $object1 = new NamedNodeImpl(new RdfHelpers(), 'http://saft/testquad/o1');
        $graph1 = new NamedNodeImpl(new RdfHelpers(), 'http://saft/testquad/g1');
        return new StatementImpl($subject1, $predicate1, $object1, $graph1);
    }

    protected function getTestTriple()
    {
        $subject2 = new NamedNodeImpl(new RdfHelpers(), 'http://saft/testtriple/s2');
        $predicate2 = new NamedNodeImpl(new RdfHelpers(), 'http://saft/testtriple/p2');
        $object2 = new NamedNodeImpl(new RdfHelpers(), 'http://saft/testtriple/o2');
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
        $subject2 = new NamedNodeImpl(new RdfHelpers(), 'http://saft/test/s1');
        $predicate2 = new NamedNodeImpl(new RdfHelpers(), 'http://saft/test/p2');
        $object2 = new LiteralImpl(new RdfHelpers(), 'John');
        return new StatementImpl($subject2, $predicate2, $object2, $this->testGraph);
    }

    /**
     * Tests add and delete statements on default graph
     */
    public function testAddAndDeleteStatementsOnDefaultGraph()
    {
        $stmtOne = new StatementImpl(
            new NamedNodeImpl(new RdfHelpers(), 'http://s/'),
            new NamedNodeImpl(new RdfHelpers(), 'http://p/'),
            new NamedNodeImpl(new RdfHelpers(), 'http://o/')
        );
        $stmtTwo = new StatementImpl(
            new NamedNodeImpl(new RdfHelpers(), 'http://s/'),
            new NamedNodeImpl(new RdfHelpers(), 'http://p/'),
            new LiteralImpl(new RdfHelpers(), 'test literal')
        );

        if ($this->fixture->hasMatchingStatement($stmtOne) || $this->fixture->hasMatchingStatement($stmtTwo)) {
            $this->markTestSkipped("Skip this test, because one of our test triples already exists");
        }

        // 2 triples
        $statements = new ArrayStatementIteratorImpl(array($stmtOne, $stmtTwo));

        // add triples
        $this->fixture->addStatements($statements);

        // graph has the two entries
        $this->assertTrue($this->fixture->hasMatchingStatement($stmtOne));
        $this->assertTrue($this->fixture->hasMatchingStatement($stmtTwo));

        $this->fixture->deleteMatchingStatements($stmtOne);
        $this->fixture->deleteMatchingStatements($stmtTwo);

        // graph does not have the two entries anymore
        $this->assertFalse($this->fixture->hasMatchingStatement($stmtOne));
        $this->assertFalse($this->fixture->hasMatchingStatement($stmtTwo));
    }

    /*
     * Tests for addStatements
     */

    public function testAddStatements()
    {
        // clear test graph
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
                new NamedNodeImpl(new RdfHelpers(), 'http://s/'),
                new NamedNodeImpl(new RdfHelpers(), 'http://p/'),
                new NamedNodeImpl(new RdfHelpers(), 'http://o/')
            ),
            new StatementImpl(
                new NamedNodeImpl(new RdfHelpers(), 'http://s/'),
                new NamedNodeImpl(new RdfHelpers(), 'http://p/'),
                new LiteralImpl(new RdfHelpers(), 'test literal')
            ),
        ));

        // add triples
        $this->fixture->addStatements($statements, $this->testGraph);

        // graph has two entries
        $statements = $this->fixture->getMatchingStatements($anyStatement, $this->testGraph);
        $this->assertCountStatementIterator(2, $statements);
    }

    public function testAddStatementsInvalidStatements()
    {
        // clear test graph
        $this->fixture->query('CLEAR GRAPH <'. $this->testGraph->getUri() .'>');

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
                new NamedNodeImpl(new RdfHelpers(), 'http://s/'),
                new NamedNodeImpl(new RdfHelpers(), 'http://p/'),
                new LiteralImpl(new RdfHelpers(), 'test literal', null, 'en')
            ),
            new StatementImpl(
                new NamedNodeImpl(new RdfHelpers(), 'http://s/'),
                new NamedNodeImpl(new RdfHelpers(), 'http://p/'),
                new LiteralImpl(new RdfHelpers(), 'test literal', null, 'de')
            ),
        ));

        // add triples
        $this->fixture->addStatements($statements, $this->testGraph);

        // graph has now two entries
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
        $statement1 = $this->getTestTriple();
        $statement2 = $this->getTestStatementWithLiteral();
        $statementIterator = new ArrayStatementIteratorImpl(array($statement1, $statement2));
        $query = 'INSERT DATA {
            Graph <http://graph/> {
                '. $statement1->toNTriples() .'
                '. $statement2->toNTriples() .'
            }
        }';

        $this->assertEquals(new EmptyResultImpl(), $this->fixture->query($query));
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
                new NamedNodeImpl(new RdfHelpers(), 'http://s/'),
                new NamedNodeImpl(new RdfHelpers(), 'http://p/'),
                new NamedNodeImpl(new RdfHelpers(), 'http://o/'),
                $this->testGraph
            ),
            new StatementImpl(
                new NamedNodeImpl(new RdfHelpers(), 'http://s/'),
                new NamedNodeImpl(new RdfHelpers(), 'http://p/'),
                new LiteralImpl(new RdfHelpers(), 'test literal'),
                $this->testGraph
            ),
        ));

        // add triples
        $this->fixture->addStatements($statements);

        // graph has two entries
        $statements = $this->fixture->getMatchingStatements($anyStatement, $this->testGraph);
        $this->assertCountStatementIterator(2, $statements);
    }

    public function testAddStatementsWithArray()
    {
        // clear test graph
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
        $statements = array(
            new StatementImpl(
                new NamedNodeImpl(new RdfHelpers(), 'http://s/'),
                new NamedNodeImpl(new RdfHelpers(), 'http://p/'),
                new NamedNodeImpl(new RdfHelpers(), 'http://o/')
            ),
            new StatementImpl(
                new NamedNodeImpl(new RdfHelpers(), 'http://s/'),
                new NamedNodeImpl(new RdfHelpers(), 'http://p/'),
                new LiteralImpl(new RdfHelpers(), 'test literal')
            ),
        );

        // add triples
        $this->fixture->addStatements($statements, $this->testGraph);

        // graph has two entries
        $statements = $this->fixture->getMatchingStatements($anyStatement, $this->testGraph);
        $this->assertCountStatementIterator(2, $statements);
    }

    /*
     * Tests for createGraph
     */

    public function testCreateGraph()
    {
        // no matter what, remove test graph
        $this->fixture->dropGraph($this->testGraph);

        // check that there is no test graph
        $graphs = $this->fixture->getGraphs();
        $this->assertFalse(isset($graphs[$this->testGraph->getUri()]));

        // create test graph
        $this->fixture->createGraph($this->testGraph);

        // check that there is a test graph
        $graphs = $this->fixture->getGraphs();
        $this->assertTrue(isset($graphs[$this->testGraph->getUri()]));
    }

    /*
     * Tests for dropGraph
     */

    // We can drop the graph and create a graph, but we can't assume any action since a store might
    // not support empty graphs.
    public function testDropGraph()
    {
        // create test graph (even if it already exists)
        $this->fixture->createGraph($this->testGraph);

        // check that the graph exists
        $graphs = $this->fixture->getGraphs();
        $this->assertTrue(isset($graphs[$this->testGraph->getUri()]));

        // drop graph
        $this->fixture->dropGraph($this->testGraph);

        // check that the graph was dropped
        $graphs = $this->fixture->getGraphs();
        $this->assertFalse(isset($graphs[$this->testGraph->getUri()]));
    }

    /*
     * Tests for deleteMatchingStatements
     */

    public function testDeleteMatchingStatements()
    {
        /*
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
                new NamedNodeImpl(new RdfHelpers(), 'http://s/'),
                new NamedNodeImpl(new RdfHelpers(), 'http://p/'),
                new NamedNodeImpl(new RdfHelpers(), 'http://o/')
            ),
            new StatementImpl(
                new NamedNodeImpl(new RdfHelpers(), 'http://s/'),
                new NamedNodeImpl(new RdfHelpers(), 'http://p/'),
                new LiteralImpl(new RdfHelpers(), 'test literal')
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
            new StatementImpl(new NamedNodeImpl(new RdfHelpers(), 'http://s/'), new NamedNodeImpl(new RdfHelpers(), 'http://p/'), new AnyPatternImpl()),
            $this->testGraph
        );

        // count no triples
        $statements = $this->fixture->getMatchingStatements($anyStatement, $this->testGraph);
        $this->assertCountStatementIterator(0, $statements);
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
                new NamedNodeImpl(new RdfHelpers(), 'http://s/'),
                new NamedNodeImpl(new RdfHelpers(), 'http://p/'),
                new NamedNodeImpl(new RdfHelpers(), 'http://o/'),
                $this->testGraph
            ),
            new StatementImpl(
                new NamedNodeImpl(new RdfHelpers(), 'http://s/'),
                new NamedNodeImpl(new RdfHelpers(), 'http://p/'),
                new LiteralImpl(new RdfHelpers(), 'test literal'),
                $this->testGraph
            ),
        ));

        // add triples
        $this->fixture->addStatements($statements, $this->testGraph);

        // count two triples
        $statements = $this->fixture->getMatchingStatements($anyStatement);
        $this->assertCountStatementIterator(2, $statements);

        /*
         * drop all triples
         */
        $this->fixture->deleteMatchingStatements(
            new StatementImpl(
                new NamedNodeImpl(new RdfHelpers(), 'http://s/'),
                new NamedNodeImpl(new RdfHelpers(), 'http://p/'),
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
                new NamedNodeImpl(new RdfHelpers(), 'http://s/'),
                new NamedNodeImpl(new RdfHelpers(), 'http://p/'),
                new NamedNodeImpl(new RdfHelpers(), 'http://o/')
            ),
            new StatementImpl(
                new NamedNodeImpl(new RdfHelpers(), 'http://s/'),
                new NamedNodeImpl(new RdfHelpers(), 'http://p/'),
                new LiteralImpl(new RdfHelpers(), 'test literal')
            ),
        ));

        // add triples
        $this->fixture->addStatements($statements, $this->testGraph);

        // count two triples
        $statements = $this->fixture->getMatchingStatements($anyStatement, $this->testGraph);
        $this->assertCountStatementIterator(2, $statements);

        /*
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

    public function testDeleteMatchingStatementsQuadRecognition()
    {
        /**
         * create test data
         */
        $this->fixture->createGraph($this->testGraph);

        $this->assertEquals(0, $this->countTriples($this->testGraph));

        $s = new NamedNodeImpl(new RdfHelpers(), 'http://saft/s');
        $p = new NamedNodeImpl(new RdfHelpers(), 'http://saft/p');
        $o = new NamedNodeImpl(new RdfHelpers(), 'http://saft/o');
        $statement = new StatementImpl($s, $p, $o, $this->testGraph);
        $this->fixture->addStatements(array($statement));

        // check that test data was created
        $this->assertEquals(1, $this->countTriples($this->testGraph));

        /*
         * remove test data via query
         */
        $this->fixture->deleteMatchingStatements($statement);

        // check that test data was removed
        $this->assertEquals(0, $this->countTriples($this->testGraph));
    }

    public function testDeleteMatchingStatementsStatementsWithLiteral()
    {
        /**
         * create test data
         */
        $this->fixture->createGraph($this->testGraph);

        $this->assertEquals(0, $this->countTriples($this->testGraph));

        $statements = array($this->getTestTriple(), $this->getTestStatementWithLiteral());
        $this->fixture->addStatements($statements, $this->testGraph);

        // check that test data was created
        $this->assertEquals(2, $this->countTriples($this->testGraph));

        /*
         * remove test data via query
         */
        $this->fixture->deleteMatchingStatements($this->getTestPatternStatement(), $this->testGraph);

        // check that test data was removed
        $this->assertEquals(0, $this->countTriples($this->testGraph));
    }

    /*
     * Tests for getGraphs
     */

    public function testGetGraphs()
    {
        $this->fixture->createGraph($this->testGraph);

        // FYI: $availableGraphs is an array containing graph URI strings as key and a
        // respective NamedNode as value.
        $availableGraphs = $this->fixture->getGraphs();

        $graphUri = $this->testGraph->getUri();

        // check, that our test graph is part of the array
        $this->assertTrue(isset($availableGraphs[$graphUri]), "The test graph is not available");
        $this->assertTrue(
            $this->testGraph->equals($availableGraphs[$graphUri]),
            "Graph object is not equal to the test graph."
        );
    }

    /*
     * Tests for getMatchingStatements
     */

    public function testGetMatchingStatements()
    {
        // 2 triples
        $statements = new ArrayStatementIteratorImpl(array(
            new StatementImpl(
                new NamedNodeImpl(new RdfHelpers(), 'http://s/'),
                new NamedNodeImpl(new RdfHelpers(), 'http://p/'),
                new NamedNodeImpl(new RdfHelpers(), 'http://o/')
            ),
            new StatementImpl(
                new NamedNodeImpl(new RdfHelpers(), 'http://s/'),
                new NamedNodeImpl(new RdfHelpers(), 'http://p/'),
                new LiteralImpl(new RdfHelpers(), 'test literal')
            ),
        ));

        // add triples
        $this->fixture->addStatements($statements, $this->testGraph);

        $statement = new StatementImpl(
            new NamedNodeImpl(new RdfHelpers(), 'http://s/'),
            new NamedNodeImpl(new RdfHelpers(), 'http://p/'),
            new AnyPatternImpl()
        );

        /*
         * Build StatementIterator instance to check against
         */
        $instanceToCheckAgainst = new ArrayStatementIteratorImpl(array(
            new StatementImpl(
                new NamedNodeImpl(new RdfHelpers(), 'http://s/'),
                new NamedNodeImpl(new RdfHelpers(), 'http://p/'),
                new NamedNodeImpl(new RdfHelpers(), 'http://o/'),
                $this->testGraph
            ),
            new StatementImpl(
                new NamedNodeImpl(new RdfHelpers(), 'http://s/'),
                new NamedNodeImpl(new RdfHelpers(), 'http://p/'),
                new LiteralImpl(new RdfHelpers(), 'test literal'),
                $this->testGraph
            )
        ));

        $this->assertStatementIteratorEquals(
            $instanceToCheckAgainst,
            $this->fixture->getMatchingStatements($statement, $this->testGraph)
        );
    }

    public function testGetMatchingStatementsEmptyGraph()
    {
        // clear test graph
        $this->fixture->query('CLEAR GRAPH <'. $this->testGraph .'>');

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
                new NamedNodeImpl(new RdfHelpers(), 'http://s/'),
                new NamedNodeImpl(new RdfHelpers(), 'http://p/'),
                new NamedNodeImpl(new RdfHelpers(), 'http://o/')
            ),
            new StatementImpl(
                new NamedNodeImpl(new RdfHelpers(), 'http://s/'),
                new NamedNodeImpl(new RdfHelpers(), 'http://p/'),
                new LiteralImpl(new RdfHelpers(), 'test literal')
            ),
        ));

        // add triples
        $this->fixture->addStatements($statements, $this->testGraph);

        $statement = new StatementImpl(
            new NamedNodeImpl(new RdfHelpers(), 'http://s/'),
            new NamedNodeImpl(new RdfHelpers(), 'http://p/'),
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

    public function testGetMatchingStatementsCheckForTriples()
    {
        $this->fixture->createGraph($this->testGraph);

        // add triples
        $this->fixture->addStatements(array($this->getTestTriple()), $this->testGraph);

        $this->assertEquals(1, $this->countTriples($this->testGraph));

        $iterator = $this->fixture->getMatchingStatements($this->getTestPatternStatement(), $this->testGraph);

        foreach ($iterator as $statement) {
            $this->assertTrue($statement->isQuad());
        }
    }

    public function testGetMatchingStatementsCheckForTriplesDefaultGraph()
    {
        $this->fixture->createGraph($this->testGraph);

        // add triples
        $this->fixture->addStatements(array($this->getTestTriple()), $this->testGraph);

        $this->assertEquals(1, $this->countTriples($this->testGraph));

        $iterator = $this->fixture->getMatchingStatements($this->getTestPatternStatement());

        foreach ($iterator as $statement) {
            $this->assertTrue($statement->isTriple());
            // one is enough, if there are more ignore them to avoid iterating over thousands of triples
            break;
        }
    }

    public function testGetMatchingStatementsFromAnyGraph()
    {
        $this->fixture->dropGraph($this->testGraph);

        // 2 triples
        $statements = new ArrayStatementIteratorImpl(array(
            new StatementImpl(
                new NamedNodeImpl(new RdfHelpers(), 'http://s/'),
                new NamedNodeImpl(new RdfHelpers(), 'http://p/'),
                new NamedNodeImpl(new RdfHelpers(), 'http://o1/'),
                new NamedNodeImpl(new RdfHelpers(), 'http://graph/a')
            ),
            new StatementImpl(
                new NamedNodeImpl(new RdfHelpers(), 'http://s/'),
                new NamedNodeImpl(new RdfHelpers(), 'http://p/'),
                new NamedNodeImpl(new RdfHelpers(), 'http://o2/'),
                new NamedNodeImpl(new RdfHelpers(), 'http://graph/b')
            ),
        ));

        // add triples
        $this->fixture->addStatements($statements);

        $statement = new StatementImpl(
            new NamedNodeImpl(new RdfHelpers(), 'http://s/'),
            new NamedNodeImpl(new RdfHelpers(), 'http://p/'),
            new AnyPatternImpl(),
            new AnyPatternImpl()
        );

        $iterator = $this->fixture->getMatchingStatements($statement);

        $statementCount = 0;
        foreach ($iterator as $statement) {
            if ($statement->getObject()->getUri() == 'http://o1/') {
                ++$statementCount;
                $this->assertTrue($statement->isQuad());
                $this->assertEquals('http://graph/a', $statement->getGraph()->getUri());
            } elseif ($statement->getObject()->getUri() == 'http://o2/') {
                ++$statementCount;
                $this->assertTrue($statement->isQuad());
                $this->assertEquals('http://graph/b', $statement->getGraph()->getUri());
            }
        }
        $this->assertEquals(2, $statementCount);
    }

    public function testGetMatchingStatementsReturnType()
    {
        $statement = new StatementImpl(
            new NamedNodeImpl(new RdfHelpers(), 'http://s/'),
            new NamedNodeImpl(new RdfHelpers(), 'http://p/'),
            new AnyPatternImpl()
        );

        $iterator = $this->fixture->getMatchingStatements($statement);

        $this->assertTrue(
            $iterator instanceof StatementIterator,
            "Get Matching Statements has to return a StatementIterator"
        );
    }

    /*
     * Tests for getStoreDescription
     */

    // Test if an array for the store description is returned
    public function testGetStoreDescription()
    {
        $this->assertTrue(is_array($this->fixture->getStoreDescription()));
    }

    /*
     * Tests for hasMatchingStatements
     */

    public function testHasMatchingStatement()
    {
        // 2 triples
        $statements = new ArrayStatementIteratorImpl(array(
            new StatementImpl(
                new NamedNodeImpl(new RdfHelpers(), 'http://s/'),
                new NamedNodeImpl(new RdfHelpers(), 'http://p/'),
                new NamedNodeImpl(new RdfHelpers(), 'http://o/')
            ),
            new StatementImpl(
                new NamedNodeImpl(new RdfHelpers(), 'http://s/'),
                new NamedNodeImpl(new RdfHelpers(), 'http://p/'),
                new LiteralImpl(new RdfHelpers(), 'test literal')
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
        $quad = $this->getTestQuad();

        $this->fixture->query('CLEAR GRAPH <'. $quad->getGraph()->getUri() .'>');

        $query = 'ASK { '. $this->rdfHelpers->statementsToSparqlFormat([$quad]) .'}';

        $this->assertEquals(new ValueResultImpl(false), $this->fixture->query($query));
    }

    /*
     * Tests for query
     */

    public function testQuery()
    {
        $this->fixture->query('CLEAR GRAPH <'. $this->testGraph->getUri() .'>');

        // 2 triples
        $statements = new ArrayStatementIteratorImpl(array(
            new StatementImpl(
                new NamedNodeImpl(new RdfHelpers(), 'http://s/'),
                new NamedNodeImpl(new RdfHelpers(), 'http://p/'),
                new NamedNodeImpl(new RdfHelpers(), 'http://o/')
            ),
            new StatementImpl(
                new NamedNodeImpl(new RdfHelpers(), 'http://s/'),
                new NamedNodeImpl(new RdfHelpers(), 'http://p/'),
                new LiteralImpl(
                    new RdfHelpers(),
                    'foobar',
                    new NamedNodeImpl(new RdfHelpers(), 'http://www.w3.org/1999/02/22-rdf-syntax-ns#langString'),
                    'en'
                )
            ),
            new StatementImpl(
                new NamedNodeImpl(new RdfHelpers(), 'http://s/'),
                new NamedNodeImpl(new RdfHelpers(), 'http://p/'),
                new LiteralImpl(new RdfHelpers(), '42')
            ),
        ));

        // add triples
        $this->fixture->addStatements($statements, $this->testGraph);

        /*
         * Build SetResult instance to check against
         */
        $setResultToCheckAgainst = new SetResultImpl(
            new \ArrayIterator(
                array(
                    array(
                        's' => new NamedNodeImpl(new RdfHelpers(), 'http://s/'),
                        'o' =>
                        new LiteralImpl(
                            new RdfHelpers(),
                            'foobar',
                            new NamedNodeImpl(new RdfHelpers(), 'http://www.w3.org/1999/02/22-rdf-syntax-ns#langString'),
                            'en'
                        )
                    ),
                    array(
                        's' => new NamedNodeImpl(new RdfHelpers(), 'http://s/'),
                        'o' => new LiteralImpl(new RdfHelpers(), '42')
                    ),
                    array(
                        's' => new NamedNodeImpl(new RdfHelpers(), 'http://s/'),
                        'o' => new NamedNodeImpl(new RdfHelpers(), 'http://o/')
                    )
                )
            )
        );
        $setResultToCheckAgainst->setVariables(array('s', 'o'));

        // check
        $this->assertResultEquals(
            $setResultToCheckAgainst,
            $this->fixture->query(
                'SELECT ?s ?o FROM <' . $this->testGraph->getUri() . '> WHERE {?s ?p ?o.} ORDER BY ?o'
            )
        );
    }

    public function testQueryAddAndQueryStatementsDefaultGraph()
    {
        // insert test data
        $this->fixture->query('INSERT DATA {
            <http://example.org/a> <http://example.org/b> <http://example.org/c>
        }');

        // select test data
        $result = $this->fixture->query('SELECT * {
            <http://example.org/a> ?p ?o
        }');

        $resultFactory = new ResultFactoryImpl();

        $this->assertEquals(
            $resultFactory->createSetResult(array(
                array(
                    'p' => new NamedNodeImpl(new RdfHelpers(), 'http://example.org/b'),
                    'o' => new NamedNodeImpl(new RdfHelpers(), 'http://example.org/c')
                )
            )),
            $result
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

        // check that graph is empty
        $statements = $this->fixture->getMatchingStatements($anyStatement, $this->testGraph);
        $this->assertCountStatementIterator(0, $statements);

        // 1 triple
        $statements = new ArrayStatementIteratorImpl(array(
            new StatementImpl(
                new NamedNodeImpl(new RdfHelpers(), 'http://s/'),
                new NamedNodeImpl(new RdfHelpers(), 'http://p/'),
                new NamedNodeImpl(new RdfHelpers(), 'http://o/')
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

    // checks if query returns a statements list for the given CONSTRUCT query
    public function testQueryConstruct()
    {
        // add test data
        $statements = array($this->getTestTriple(), $this->getTestQuad());
        $this->fixture->addStatements($statements, $this->testGraph);

        $resultFactory = new ResultFactoryImpl();

        $this->assertResultEquals(
            $resultFactory->createStatementResult(array(
                new StatementImpl(
                    new NamedNodeImpl(new RdfHelpers(), 'http://saft/testquad/p1'),
                    new NamedNodeImpl(new RdfHelpers(), 'http://saft/testquad/s1'),
                    new NamedNodeImpl(new RdfHelpers(), 'http://saft/testquad/o1')
                ),
                new StatementImpl(
                    new NamedNodeImpl(new RdfHelpers(), 'http://saft/testtriple/p2'),
                    new NamedNodeImpl(new RdfHelpers(), 'http://saft/testtriple/s2'),
                    new NamedNodeImpl(new RdfHelpers(), 'http://saft/testtriple/o2')
                )
            )),
            $this->fixture->query(
                'CONSTRUCT { ?p1 ?s1 ?o1 } FROM <'. $this->testGraph->getUri() .'> WHERE {?s1 ?p1 ?o1.}'
            )
        );
    }

    // graph is empty; check result for a given CONSTRUCT query
    public function testQueryConstructEmptyGraph()
    {
        $this->fixture->query('CLEAR GRAPH <'. $this->testGraph .'>');

        $this->assertEquals(
            new EmptyResultImpl(),
            $this->fixture->query(
                'CONSTRUCT { ?p1 ?s1 ?o1 } FROM <'. $this->testGraph->getUri() .'> WHERE {?s1 ?p1 ?o1.}'
            )
        );
    }

    public function testQueryEmptyResult()
    {
        // clear test graph
        $this->fixture->query('CLEAR GRAPH <'. $this->testGraph .'>');

        $result = $this->fixture->query(
            'SELECT ?s ?p ?o FROM <'. $this->testGraph->getUri() .'> WHERE {?s ?p ?o.}'
        );

        // check that variables are set right and there is no content
        $this->assertEquals(array('s', 'p', 'o'), $result->getVariables());
        $this->assertCountStatementIterator(0, $result);
    }

    public function testQueryDeleteMultipleStatementsVariablePatterns()
    {
        /**
         * create test data
         */
        $this->fixture->createGraph($this->testGraph);

        $this->assertEquals(0, $this->countTriples($this->testGraph));

        $statements = array($this->getTestTriple(), $this->getTestQuad());
        $this->fixture->addStatements($statements, $this->testGraph);

        // check that test data was created
        $this->assertEquals(2, $this->countTriples($this->testGraph));

        /*
         * remove test data via query
         */
        $triplePart = $this->rdfHelpers->statementsToSparqlFormat(array($this->getTestPatternStatement()));
        $query = 'DELETE WHERE { Graph <'. $this->testGraph .'> {'. $triplePart .'}}';

        $this->assertTrue($this->fixture->query($query)->isEmptyResult());

        // check that test data was removed
        $this->assertEquals(0, $this->countTriples($this->testGraph));
    }

    public function testQueryDeleteMultipleStatementsStatementsWithLiteral()
    {
        /**
         * create test data
         */
        $this->fixture->query('CLEAR GRAPH <'. $this->testGraph->getUri() .'>');

        $this->assertEquals(0, $this->countTriples($this->testGraph));

        $subject2 = new NamedNodeImpl(new RdfHelpers(), 'http://saft/test/s1');
        $predicate2 = new NamedNodeImpl(new RdfHelpers(), 'http://saft/test/p2');
        $object2 = new LiteralImpl(new RdfHelpers(), 'Emma');
        $statement = new StatementImpl($subject2, $predicate2, $object2);

        // add two statements with literal
        $statements = array($this->getTestStatementWithLiteral(), $statement);
        $this->fixture->addStatements($statements, $this->testGraph);

        // check that test data was created
        $this->assertEquals(2, $this->countTriples($this->testGraph));

        /*
         * remove test data via query
         */
        $triplePart = $this->rdfHelpers->statementsToSparqlFormat(array($this->getTestPatternStatement()));
        $query = 'DELETE WHERE { Graph <'. $this->testGraph .'> {'. $triplePart .'}}';

        $this->assertTrue($this->fixture->query($query)->isEmptyResult());

        // check that test data was removed
        $this->assertEquals(0, $this->countTriples($this->testGraph));
    }

    // tests query function how it reacts if nothing was found.
    public function testQueryScenarioEmptyResult()
    {
        $this->fixture->dropGraph($this->testGraph);

        // empty graph
        $this->assertEquals(0, $this->countTriples($this->testGraph));

        $result = $this->fixture->query('SELECT * FROM <'. $this->testGraph->getUri() .'> WHERE {
            ?s ?p ?o.
            ?s rdf:type foo:Bar .
        }');

        $this->assertCountStatementIterator(0, $result);
    }
}
