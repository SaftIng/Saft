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

namespace Saft\Addition\ARC2\Test\Store;

use Saft\Addition\ARC2\Store\ARC2;
use Saft\Rdf\CommonNamespaces;
use Saft\Rdf\NamedNode;
use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\RdfHelpers;
use Saft\Rdf\StatementFactoryImpl;
use Saft\Rdf\StatementImpl;
use Saft\Rdf\StatementIteratorFactoryImpl;
use Saft\Sparql\Result\ResultFactoryImpl;
use Saft\Rdf\Test\TestCase;
use Symfony\Component\Cache\Simple\ArrayCache;

class ARC2Test extends TestCase
{
    public function setUp()
    {
        global $dbConfig;

        parent::setUp();

        // use array as cache, to avoid missleading results
        $dbConfig['cache_instance'] = new ArrayCache();

        $this->fixture = new ARC2(
            $this->nodeFactory,
            $this->statementFactory,
            new ResultFactoryImpl(),
            new StatementIteratorFactoryImpl(),
            $this->rdfHelpers,
            $this->commonNamespaces,
            $dbConfig
        );

        $this->fixture->dropGraph($this->testGraph);
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
            'SELECT COUNT(?s) as ?count FROM <'.$graph->getUri().'> WHERE {?s ?p ?o}'
        );

        $variables = $result->getVariables();
        $variable = array_shift($variables);
        $entry = $result->current();

        return $entry[$variable]->getValue();
    }

    /*
     * Tests dropGraph
     */

    public function testDropGraphEffects()
    {
        $secondGraph = $this->nodeFactory->createNamedNode($this->testGraph->getUri().'2');

        $this->fixture->createGraph($secondGraph);

        $this->fixture->query('CLEAR GRAPH <'.$this->testGraph->getUri().'>');
        $this->fixture->query('CLEAR GRAPH <'.$secondGraph->getUri().'>');

        // fill graph 1
        $this->fixture->addStatements(
            [
                new StatementImpl(
                    $this->testGraph,
                    $this->testGraph,
                    $this->testGraph,
                    $this->testGraph
                ),
            ]
        );

        // fill graph 2
        $this->fixture->addStatements(
            [
                new StatementImpl(
                    $secondGraph,
                    $secondGraph,
                    $secondGraph,
                    $secondGraph
                ),
            ]
        );

        $result1 = $this->fixture->query('SELECT * FROM <'.$secondGraph->getUri().'> WHERE {?s ?p ?o}');

        $this->assertEquals(1, \count($result1));

        // remove graph 1
        $this->fixture->dropGraph($this->testGraph);

        $result2 = $this->fixture->query('SELECT * FROM <'.$secondGraph->getUri().'> WHERE {?s ?p ?o}');

        $this->fixture->dropGraph($secondGraph);

        $this->assertEquals(1, \count($result2));
    }

    /*
     * Tests for getGraphs
     */

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Not implemented, because ARC2 creates graphs on demand. Empty graphs are not supported in ARC2.
     */
    public function testGetGraphs()
    {
        $this->fixture->getGraphs();
    }

    /*
     * Tests openConnection
     */

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage ARC2: Field db_name is not set.
     */
    public function testOpenConnectionCheckDatabase()
    {
        new ARC2(
            new NodeFactoryImpl(),
            new StatementFactoryImpl(),
            new ResultFactoryImpl(),
            new StatementIteratorFactoryImpl(),
            new RdfHelpers(),
            new CommonNamespaces(),
            []
        );
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage ARC2: Field db_user is not set.
     */
    public function testOpenConnectionCheckUsername()
    {
        new ARC2(
            new NodeFactoryImpl(),
            new StatementFactoryImpl(),
            new ResultFactoryImpl(),
            new StatementIteratorFactoryImpl(),
            new RdfHelpers(),
            new CommonNamespaces(),
            [
                'db_host' => 'localhost',
                'db_name' => 'saft',
            ]
        );
    }
}
