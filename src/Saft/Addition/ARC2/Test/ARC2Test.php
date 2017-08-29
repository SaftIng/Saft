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

namespace Saft\Addition\ARC2\Test;

use Saft\Addition\ARC2\Store\ARC2;
use Saft\Rdf\AnyPatternImpl;
use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\CommonNamespaces;
use Saft\Rdf\LiteralImpl;
use Saft\Rdf\NamedNode;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\RdfHelpers;
use Saft\Rdf\StatementFactoryImpl;
use Saft\Rdf\StatementImpl;
use Saft\Rdf\StatementIteratorFactoryImpl;
use Saft\Sparql\Query\QueryFactoryImpl;
use Saft\Sparql\SparqlUtils;
use Saft\Sparql\Query\QueryUtils;
use Saft\Sparql\Result\ResultFactoryImpl;
use Saft\Sparql\Result\SetResultImpl;
use Saft\Store\Test\StoreAbstractTest;
use Symfony\Component\Yaml\Parser;

class ARC2Test extends StoreAbstractTest
{
    public function setUp()
    {
        parent::setUp();

        if (defined('IN_TRAVIS')) {
            $this->loadTestConfiguration(__DIR__ .'/../test-config-travis.yml');
        } else {
            $this->loadTestConfiguration(__DIR__ .'/../test-config.yml');
        }

        if (true === isset($this->configuration['arc2Config'])) {
            try {
                $this->fixture = new ARC2(
                    $this->nodeFactory,
                    $this->statementFactory,
                    new QueryFactoryImpl(new RdfHelpers()),
                    new ResultFactoryImpl(),
                    new StatementIteratorFactoryImpl(),
                    $this->rdfHelpers,
                    $this->commonNamespaces,
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
        $secondGraph = $this->nodeFactory->createNamedNode($this->testGraph->getUri() . '2');

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
            new NodeFactoryImpl(new RdfHelpers()),
            new StatementFactoryImpl(),
            new QueryFactoryImpl(new RdfHelpers()),
            new ResultFactoryImpl(),
            new StatementIteratorFactoryImpl(),
            new RdfHelpers(),
            new CommonNamespaces(),
            array()
        );
    }

    // expect exception because of missing host
    public function testOpenConnectionCheckHost()
    {
        $this->setExpectedException('Exception');

        new ARC2(
            new NodeFactoryImpl(new RdfHelpers()),
            new StatementFactoryImpl(),
            new QueryFactoryImpl(new RdfHelpers()),
            new ResultFactoryImpl(),
            new StatementIteratorFactoryImpl(),
            new RdfHelpers(),
            new CommonNamespaces(),
            array('database' => 'saft')
        );
    }

    // expect exception because of missing username
    public function testOpenConnectionCheckUsername()
    {
        $this->setExpectedException('Exception');

        new ARC2(
            new NodeFactoryImpl(new RdfHelpers()),
            new StatementFactoryImpl(),
            new QueryFactoryImpl(new RdfHelpers()),
            new ResultFactoryImpl(),
            new StatementIteratorFactoryImpl(),
            new RdfHelpers(),
            new CommonNamespaces(),
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

    // test if ARC2 merges blank nodes
    public function testQueryRegressionBlankNodeHandling()
    {
        $this->fixture->addStatements(array(
            $this->statementFactory->createStatement(
                $this->nodeFactory->createNamedNode('http://foo/bar1'),
                $this->nodeFactory->createNamedNode('http://www.w3.org/1999/02/22-rdf-syntax-ns#type'),
                $this->nodeFactory->createNamedNode('http://foo/bar2'),
                $this->testGraph
            ),
            $this->statementFactory->createStatement(
                $this->nodeFactory->createNamedNode('http://foo/bar1'),
                $this->nodeFactory->createNamedNode('http://foo/bar2'),
                $this->nodeFactory->createLiteral('baz'),
                $this->testGraph
            ),
            $this->statementFactory->createStatement(
                $this->nodeFactory->createBlankNode('b0'),
                $this->nodeFactory->createNamedNode('http://foo/bar4'),
                $this->nodeFactory->createLiteral('foobar'),
                $this->testGraph
            ),
            $this->statementFactory->createStatement(
                $this->nodeFactory->createNamedNode('http://foo/bar1'),
                $this->nodeFactory->createNamedNode('http://foo/bar3'),
                $this->nodeFactory->createBlankNode('b0'),
                $this->testGraph
            ),
            $this->statementFactory->createStatement(
                $this->nodeFactory->createBlankNode('b1'),
                $this->nodeFactory->createNamedNode('http://foo/event'),
                $this->nodeFactory->createNamedNode('http://foo/foobar2'),
                $this->testGraph
            ),
            $this->statementFactory->createStatement(
                $this->nodeFactory->createBlankNode('b1'),
                $this->nodeFactory->createNamedNode('http://foo/foobaz'),
                $this->nodeFactory->createLiteral('true'),
                $this->testGraph
            ),
            $this->statementFactory->createStatement(
                $this->nodeFactory->createNamedNode('http://foo/bar1'),
                $this->nodeFactory->createNamedNode('http://foo/bar3'),
                $this->nodeFactory->createBlankNode('b1'),
                $this->testGraph
            ),
            $this->statementFactory->createStatement(
                $this->nodeFactory->createNamedNode('http://foo/Event'),
                $this->nodeFactory->createNamedNode('http://foo/baz'),
                $this->nodeFactory->createNamedNode('http://foo/baz2'),
                $this->testGraph
            ),
            $this->statementFactory->createStatement(
                $this->nodeFactory->createNamedNode('http://foo/foobar2'),
                $this->nodeFactory->createNamedNode('http://foo/baz2'),
                $this->nodeFactory->createNamedNode('http://foo/baz3'),
                $this->testGraph
            ),
        ));

        $resultToCheck = $this->fixture->query('SELECT * FROM <'. $this->testGraph .'> WHERE {?s ?p ?o.}');

        $expectedResult = new SetResultImpl(array(
            array(
                's' => $this->nodeFactory->createNamedNode('http://foo/bar1'),
                'p' => $this->nodeFactory->createNamedNode('http://www.w3.org/1999/02/22-rdf-syntax-ns#type'),
                'o' => $this->nodeFactory->createNamedNode('http://foo/bar2'),
            ),
            array(
                's' => $this->nodeFactory->createNamedNode('http://foo/bar1'),
                'p' => $this->nodeFactory->createNamedNode('http://foo/bar2'),
                'o' => $this->nodeFactory->createLiteral('baz')
            ),
            array(
                's' => $this->nodeFactory->createNamedNode('http://foo/bar1'),
                'p' => $this->nodeFactory->createNamedNode('http://foo/bar3'),
                'o' => $resultToCheck[2]['o'] // b0
            ),
            array(
                's' => $this->nodeFactory->createNamedNode('http://foo/bar1'),
                'p' => $this->nodeFactory->createNamedNode('http://foo/bar3'),
                'o' => $resultToCheck[3]['o'] // b1
            ),
            array(
                's' => $resultToCheck[2]['o'], // b0
                'p' => $this->nodeFactory->createNamedNode('http://foo/bar4'),
                'o' => $this->nodeFactory->createLiteral('foobar')
            ),
            array(
                's' => $resultToCheck[3]['o'], // b1
                'p' => $this->nodeFactory->createNamedNode('http://foo/event'),
                'o' => $this->nodeFactory->createNamedNode('http://foo/foobar2')
            ),
            array(
                's' => $resultToCheck[3]['o'], // b1
                'p' => $this->nodeFactory->createNamedNode('http://foo/foobaz'),
                'o' => $this->nodeFactory->createLiteral('true')
            ),
            array(
                's' => $this->nodeFactory->createNamedNode('http://foo/Event'),
                'p' => $this->nodeFactory->createNamedNode('http://foo/baz'),
                'o' => $this->nodeFactory->createNamedNode('http://foo/baz2')
            ),
            array(
                's' => $this->nodeFactory->createNamedNode('http://foo/foobar2'),
                'p' => $this->nodeFactory->createNamedNode('http://foo/baz2'),
                'o' => $this->nodeFactory->createNamedNode('http://foo/baz3')
            ),
        ));
        $expectedResult->setVariables(array('s', 'p', 'o'));

        $this->assertSetIteratorEquals($expectedResult, $resultToCheck);
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
                    $this->nodeFactory->createNamedNode('http://foobar/1'),
                    $this->nodeFactory->createNamedNode('http://foobar/2'),
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
