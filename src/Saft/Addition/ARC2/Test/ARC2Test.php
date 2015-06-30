<?php

namespace Saft\Addition\ARC2\Test;

use Saft\Addition\ARC2\Store\ARC2;
use Saft\Rdf\AnyPatternImpl;
use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\LiteralImpl;
use Saft\Rdf\NamedNode;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\StatementFactoryImpl;
use Saft\Rdf\StatementImpl;
use Saft\Rdf\StatementIteratorFactoryImpl;
use Saft\Sparql\Query\QueryFactoryImpl;
use Saft\Sparql\Result\ResultFactoryImpl;
use Saft\Store\Test\StoreAbstractTest;
use Symfony\Component\Yaml\Parser;

class ARC2Test extends StoreAbstractTest
{
    public function setUp()
    {
        parent::setUp();

        if (true === isset($this->config['arc2Config'])) {
            $this->fixture = new ARC2(
                new NodeFactoryImpl(),
                new StatementFactoryImpl(),
                new QueryFactoryImpl(),
                new ResultFactoryImpl(),
                new StatementIteratorFactoryImpl(),
                $this->config['arc2Config']
            );

            $this->fixture->dropGraph($this->testGraph);
            $this->fixture->createGraph($this->testGraph);

        } else {
            $this->markTestSkipped('Array arc2Config is not set in the test-config.yml.');
        }
    }

    /**
     *
     */
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

    /**
     * Tests for query
     */

    // override test from parent class because Virtuoso does not support what we want to test.
    public function testQueryAddAndQueryStatementsDefaultGraph()
    {
        // See: https://github.com/openlink/virtuoso-opensource/issues/417
        $this->markTestSkipped('ARC2 does not grant read and write access to the default graph.');
    }
}
