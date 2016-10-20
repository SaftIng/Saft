<?php

namespace Saft\Addition\ARC2\Test;

use Saft\Addition\ARC2\Store\ARC2;
use Saft\Rdf\AnyPatternImpl;
use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\LiteralImpl;
use Saft\Rdf\NamedNode;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\NodeUtils;
use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\StatementFactoryImpl;
use Saft\Rdf\StatementImpl;
use Saft\Rdf\StatementIteratorFactoryImpl;
use Saft\Sparql\Query\QueryFactoryImpl;
use Saft\Sparql\SparqlUtils;
use Saft\Sparql\Query\QueryUtils;
use Saft\Sparql\Result\ResultFactoryImpl;
use Saft\Store\Test\StoreAbstractTest;
use Symfony\Component\Yaml\Parser;

class ARC2Test extends StoreAbstractTest
{
    public function setUp()
    {
        parent::setUp();

        if (true === isset($this->configuration['arc2Config'])) {
            try {
                $this->fixture = new ARC2(
                    new NodeFactoryImpl(new NodeUtils()),
                    new StatementFactoryImpl(),
                    new QueryFactoryImpl(new NodeUtils(), new QueryUtils()),
                    new ResultFactoryImpl(),
                    new StatementIteratorFactoryImpl(),
                    new NodeUtils(),
                    new QueryUtils(),
                    new SparqlUtils(new StatementIteratorFactoryImpl()),
                    $this->configuration['arc2Config']
                );
            } catch (\Exception $e) {
                $this->markTestSkipped($e->getMessage());
            }

            $this->fixture->dropGraph($this->testGraph);
            $this->fixture->createGraph($this->testGraph);

        } else {
            $this->markTestSkipped('Array arc2Config is not set in the test-config.yml.');
        }
    }

    public function tearDown()
    {
        if (null !== $this->fixture) {
            $this->fixture->emptyAllTables();
        }

        parent::tearDown();
    }

    /*
     * Helper functions
     */

    // override parent version, because it does not really work.
    // https://github.com/semsol/arc2/wiki/SPARQL%2B#aggregate-example
    protected function countTriples(NamedNode $graph)
    {
        $result = $this->fixture->query(
            'SELECT COUNT(?s) as ?count FROM <'. $graph->getUri().'> WHERE {?s ?p ?o}'
        );

        $variables = $result->getVariables();
        $variable = array_shift($variables);
        $entry = $result->current();
        return $entry[$variable]->getValue();
    }

    /*
     * Tests dropGraph
     */

    // checks that droping one graph does not affects other graphs or triples, meaning, that only the right stuff
    // gets deleted.
    public function testDropGraphEffects()
    {
        $secondGraph = new NamedNodeImpl(new NodeUtils(), $this->testGraph->getUri() . '2');

        $this->fixture->createGraph($secondGraph);

        $this->fixture->query('CLEAR GRAPH <'. $this->testGraph->getUri().'>');
        $this->fixture->query('CLEAR GRAPH <'. $secondGraph->getUri().'>');

        // fill graph 1
        $this->fixture->addStatements(
            array(
                new StatementImpl(
                    $this->testGraph,
                    $this->testGraph,
                    $this->testGraph,
                    $this->testGraph
                ),
            )
        );

        // fill graph 2
        $this->fixture->addStatements(
            array(
                new StatementImpl(
                    $secondGraph,
                    $secondGraph,
                    $secondGraph,
                    $secondGraph
                ),
            )
        );

        // remove graph 1
        $this->fixture->dropGraph($this->testGraph);

        $result = $this->fixture->query('SELECT * FROM <'. $secondGraph->getUri() .'> WHERE {?s ?p ?o}');

        $this->fixture->dropGraph($secondGraph);

        $this->assertCountStatementIterator(
            1,
            $result
        );
    }

    /*
     * Tests openConnection
     */

    // expect exception because of missing database
    public function testOpenConnectionCheckDatabase()
    {
        $this->setExpectedException('Exception');

        new ARC2(
            new NodeFactoryImpl(new NodeUtils()),
            new StatementFactoryImpl(),
            new QueryFactoryImpl(new NodeUtils(), new QueryUtils()),
            new ResultFactoryImpl(),
            new StatementIteratorFactoryImpl(),
            new NodeUtils(),
            new QueryUtils(),
            new SparqlUtils(new StatementIteratorFactoryImpl()),
            array()
        );
    }

    // expect exception because of missing host
    public function testOpenConnectionCheckHost()
    {
        $this->setExpectedException('Exception');

        new ARC2(
            new NodeFactoryImpl(new NodeUtils()),
            new StatementFactoryImpl(),
            new QueryFactoryImpl(new NodeUtils(), new QueryUtils()),
            new ResultFactoryImpl(),
            new StatementIteratorFactoryImpl(),
            new NodeUtils(),
            new QueryUtils(),
            new SparqlUtils(new StatementIteratorFactoryImpl()),
            array('database' => 'saft')
        );
    }

    // expect exception because of missing username
    public function testOpenConnectionCheckUsername()
    {
        $this->setExpectedException('Exception');

        new ARC2(
            new NodeFactoryImpl(new NodeUtils()),
            new StatementFactoryImpl(),
            new QueryFactoryImpl(new NodeUtils(), new QueryUtils()),
            new ResultFactoryImpl(),
            new StatementIteratorFactoryImpl(),
            new NodeUtils(),
            new QueryUtils(),
            new SparqlUtils(new StatementIteratorFactoryImpl()),
            array('database' => 'saft', 'host' => 'localhost')
        );
    }

    /**
     * Tests for query
     */

    // override test from parent class because Virtuoso does not support what we want to test.
    public function testQueryAddAndQueryStatementsDefaultGraph()
    {
        // See: https://github.com/openlink/virtuoso-opensource/issues/417
        $this->markTestSkipped('ARC2 does not grant read and write access to the default graph.');
    }

    /**
     * Further test cases
     */

    // tests, if the adapter successfully removed all triples after a dropGraph call
    // related to https://github.com/SaftIng/Saft/issues/72
    public function testRemovalOfTriplesAfterDropGraph()
    {
        $this->markTestSkipped('Implement a way to remove all triples on drop-graph call.');

        /*
         * Count rows of all relevant ARC2 tables
         */
        $tablesToCheck = array();
        $tablesToCheck[] = $this->configuration['arc2Config']['table-prefix'] . '_g2t';
        $tablesToCheck[] = $this->configuration['arc2Config']['table-prefix'] . '_id2val';
        $tablesToCheck[] = $this->configuration['arc2Config']['table-prefix'] . '_o2val';
        $tablesToCheck[] = $this->configuration['arc2Config']['table-prefix'] . '_s2val';
        $tablesToCheck[] = $this->configuration['arc2Config']['table-prefix'] . '_triple';

        $rowCount = array();
        foreach ($tablesToCheck as $table) {
            $rowCount[$table] = $this->fixture->getRowCount($table);

            // hacky, but neccessary, because for each test we first remove the testgraph and create it new.
            // but after this test, we remove it, so the first entries from before are gone too. thats the
            // reason why the number of entries differ.
            if ($this->configuration['arc2Config']['table-prefix'] . '_g2t' == $table) {
                --$rowCount[$table];
            } elseif ($this->configuration['arc2Config']['table-prefix'] . '_id2val' == $table) {
                --$rowCount[$table];
            }
        }

        // add test triples to graph
        $this->fixture->addStatements(
            array(
                new StatementImpl(
                    $this->testGraph,
                    $this->testGraph,
                    $this->testGraph,
                    $this->testGraph
                ),
                new StatementImpl(
                    $this->testGraph,
                    new NamedNodeImpl(new NodeUtils(), 'http://foobar/1'),
                    new NamedNodeImpl(new NodeUtils(), 'http://foobar/2'),
                    $this->testGraph
                )
            )
        );

        // check that the graph contains 1 triple
        $statements = $this->fixture->getMatchingStatements(new StatementImpl(
            new AnyPatternImpl(), new AnyPatternImpl(), new AnyPatternImpl(), $this->testGraph
        ), $this->testGraph);
        $this->assertCountStatementIterator(2, $statements);

        // drop graph
        $this->fixture->dropGraph($this->testGraph);

        // check via API for matching statements of this graph
        $statements = $this->fixture->getMatchingStatements(new StatementImpl(
            new AnyPatternImpl(), new AnyPatternImpl(), new AnyPatternImpl(), $this->testGraph
        ), $this->testGraph);
        $this->assertCountStatementIterator(0, $statements);

        // check tables manually, that they only contain as many rows as before this test
        foreach ($tablesToCheck as $table) {
            $this->assertEquals(
                $this->fixture->getRowCount($table),
                $rowCount[$table],
                'In table '. $table .' are more rows as before this test, which means some were '.
                'not removed properly.'
            );
        }
    }
}
