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

namespace Saft\Addition\Virtuoso\Test;

use Saft\Addition\Virtuoso\Store\Virtuoso;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\RdfHelpers;
use Saft\Rdf\StatementImpl;
use Saft\Rdf\StatementFactoryImpl;
use Saft\Rdf\StatementIteratorFactoryImpl;
use Saft\Sparql\Query\QueryFactoryImpl;
use Saft\Sparql\Result\ResultFactoryImpl;
use Saft\Sparql\Result\SetResultImpl;
use Saft\Store\Test\AbstractStoreTest;

class VirtuosoTest extends AbstractStoreTest
{
    public function setUp()
    {
        parent::setUp();

        $this->loadTestConfiguration(__DIR__.'/../../test-config.yml');

        try {
            $this->isTestPossible();
            $this->fixture = new Virtuoso(
                new NodeFactoryImpl(),
                new StatementFactoryImpl(),
                new QueryFactoryImpl(new RdfHelpers()),
                new ResultFactoryImpl(),
                new StatementIteratorFactoryImpl(),
                new RdfHelpers(),
                $this->configuration['virtuosoConfig']
            );
        } catch (\Exception $e) {
            $this->markTestSkipped($e->getMessage());
        }
    }

    protected function isTestPossible()
    {
        if (false === isset($this->configuration['virtuosoConfig'])) {
            throw new \Exception('Array virtuosoConfig is not set in the test-config.yml.');
        } else {
            new \PDO(
                'odbc:'.(string) $this->configuration['virtuosoConfig']['dsn'],
                (string) $this->configuration['virtuosoConfig']['username'],
                (string) $this->configuration['virtuosoConfig']['password']
            );
        }

        return true;
    }

    /*
     * Tests to check add and delete statements on default graph.
     */

    // override test from parent class because Virtuoso does not support what we want to test.
    public function testAddAndDeleteStatementsOnDefaultGraph()
    {
        // See: https://github.com/openlink/virtuoso-opensource/issues/417
        $this->markTestSkipped('Virtuoso does not grant write access to the default graph.');
    }

    /*
     * Tests for addStatements
     */

    /**
     * Tests add statements on default graph. It is expected that an exception will is thrown, because
     * no graph information were given.
     */
    public function testAddStatementsOnDefaultGraphWithException()
    {
        $stmtOne = new StatementImpl(
            new NamedNodeImpl('http://add/delete/defaultgraph/s/'),
            new NamedNodeImpl('http://add/delete/defaultgraph/p/'),
            new NamedNodeImpl('http://add/delete/defaultgraph/o/')
        );

        $this->setExpectedException('\Exception');
        $this->fixture->addStatements([$stmtOne]);
    }

    /*
     * Tests for deleteMatchingStatements
     */

    /**
     * Tests delete matching statements on default graph. It is expected that an exception will is thrown,
     * because no graph information were given.
     */
    public function testDeleteMatchingStatementsOnDefaultGraphWithException()
    {
        $stmtOne = new StatementImpl(
            new NamedNodeImpl('http://add/delete/defaultgraph/s/'),
            new NamedNodeImpl('http://add/delete/defaultgraph/p/'),
            new NamedNodeImpl('http://add/delete/defaultgraph/o/')
        );

        $this->setExpectedException('\Exception');
        $this->fixture->deleteMatchingStatements($stmtOne);
    }

    /*
     * Tests for isGraphAvailable
     */

    public function testIsGraphAvailable()
    {
        $this->fixture->dropGraph($this->testGraph);

        $this->assertFalse($this->fixture->isGraphAvailable($this->testGraph));

        $this->fixture->createGraph($this->testGraph);

        $this->assertTrue($this->fixture->isGraphAvailable($this->testGraph));
    }

    /*
     * Tests for query
     */

    // override test from parent class because Virtuoso does not support what we want to test.
    public function testQueryAddAndQueryStatementsDefaultGraph()
    {
        // See: https://github.com/openlink/virtuoso-opensource/issues/417
        $this->markTestSkipped('Virtuoso does not grant write access to the default graph.');
    }

    // test that prefixed URIs added to the store are stored with full length.
    public function testAddStatementsWithPrefixedUris()
    {
        // clear test graph
        $this->fixture->query('CLEAR GRAPH <'.$this->testGraph->getUri().'>');

        // add triples
        $this->fixture->addStatements([
            $this->statementFactory->createStatement(
                $this->nodeFactory->createNamedNode('http://statValue/2'),
                $this->nodeFactory->createNamedNode('http://www.w3.org/1999/02/22-rdf-syntax-ns#type'),
                $this->nodeFactory->createNamedNode('http://stat/StatisticValue'),
                $this->testGraph
            ),
        ]);

        $result = $this->fixture->query('SELECT * FROM <'.$this->testGraph.'> WHERE {?s ?p ?o.}');

        $expectedResult = new SetResultImpl([
            [
                's' => $this->nodeFactory->createNamedNode('http://statValue/2'),
                'p' => $this->nodeFactory->createNamedNode('http://www.w3.org/1999/02/22-rdf-syntax-ns#type'),
                'o' => $this->nodeFactory->createNamedNode('http://stat/StatisticValue'),
            ],
        ]);
        $expectedResult->setVariables(['s', 'p', 'o']);

        $this->assertSetIteratorEquals($expectedResult, $result);
    }

    // tests how blank nodes are getting handled in the result
    public function testQueryScenarioBlankNodeHandling()
    {
        $this->markTestSkipped(
            'Virtuoso does not seemed to like BlankNodes in INSERT DATA queries.'
            .' See for more information: https://github.com/openlink/virtuoso-opensource/issues/126'
        );
    }

    /*
     * Tests for sqlQuery
     */

    public function testSqlQuery()
    {
        $re = $this->fixture->sqlQuery('SELECT * FROM DB.INFORMATION_SCHEMA.TABLES');

        $this->assertTrue(is_array($re->fetchAll(\PDO::FETCH_ASSOC)));
    }

    public function testSqlQueryInvalidQuery()
    {
        $this->setExpectedException('\Exception');

        $this->fixture->sqlQuery('invalid query');
    }

    /**
     * Regression test for https://github.com/SaftIng/Saft/issues/61
     * "Undefined index: xml:lang" in Virtuoso.
     */
    public function testQueryWithoutLanguageTag()
    {
        // create a triple with literal, which is not typed or has a language tag
        $this->fixture->query('INSERT INTO <'.$this->testGraph.'> {<http://a> <http://b> "foo"}');

        // check if that functions throws a warning about an undefined index xml:lang
        $result = $this->fixture->query('SELECT * FROM <'.$this->testGraph.'> WHERE {?s ?p ?o.}');

        // check returned result set, to be sure to have the right mapping for the literal
        foreach ($result as $key => $value) {
            $this->assertTrue(isset($value['s']));
            $this->assertTrue(isset($value['p']));
            $this->assertTrue(isset($value['o']));

            $this->assertEquals('http://a', $value['s']->getUri());
            $this->assertEquals('http://b', $value['p']->getUri());
            $this->assertEquals('foo', $value['o']->getValue());
        }

        $this->assertEquals(1, count($result));
    }
}
