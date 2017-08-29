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

        $this->fixture->query('CLEAR GRAPH <'. $this->testGraph->getUri() .'>');
        $this->fixture->query('CLEAR GRAPH <'. $secondGraph->getUri() .'>');

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

    // tests how it reacts, when it is called with multiple dropGraph calls and addStatements between
    public function testRegressionMultipleDropGraphsWithAddStatements()
    {
        // recreate graph
        $this->fixture->dropGraph($this->testGraph);
        $this->assertEquals(0, count($this->fixture->getGraphs()));

        $this->fixture->createGraph($this->testGraph);
        $this->assertEquals(1, count($this->fixture->getGraphs()));

        // add data
        $this->fixture->addStatements(array(
                $this->statementFactory->createStatement(
                    $this->nodeFactory->createNamedNode('http://foo/1'),
                    $this->nodeFactory->createNamedNode('http://foo/2'),
                    $this->nodeFactory->createNamedNode('http://foo/3')
                )
            ),
            $this->testGraph
        );

        $results = $this->fixture->query('SELECT ?s ?p ?o FROM <'. $this->testGraph .'> WHERE {?s ?p ?o.}');
        $this->assertEquals(1, count($results));

        // recreate graph
        $this->fixture->dropGraph($this->testGraph);
        $this->fixture->createGraph($this->testGraph);

        // add data
        $this->fixture->addStatements(
            array(
                $this->statementFactory->createStatement(
                    $this->nodeFactory->createNamedNode($this->testGraph . '1'),
                    $this->nodeFactory->createNamedNode($this->testGraph . '2'),
                    $this->nodeFactory->createNamedNode($this->testGraph . '3')
                )
            ),
            $this->testGraph
        );

        $results = $this->fixture->query('SELECT * FROM <'. $this->testGraph .'> WHERE {?s ?p ?o.}');
        $this->assertEquals(1, count($results));

        $this->fixture->dropGraph($this->testGraph);
    }
}
