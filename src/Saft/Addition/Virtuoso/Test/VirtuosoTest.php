<?php

namespace Saft\Addition\Virtuoso\Test;

use Saft\Addition\Virtuoso\Store\Virtuoso;
use Saft\Rdf\AnyPatternImpl;
use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\LiteralImpl;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\StatementImpl;
use Saft\Rdf\StatementFactoryImpl;
use Saft\Rdf\StatementIteratorFactoryImpl;
use Saft\Sparql\Query\QueryFactoryImpl;
use Saft\Store\Result\ResultFactoryImpl;
use Saft\Store\Test\StoreAbstractTest;
use Symfony\Component\Yaml\Parser;

class VirtuosoTest extends StoreAbstractTest
{
    public function setUp()
    {
        parent::setUp();

        if (true === isset($this->config['virtuosoConfig'])) {
            $this->fixture = new Virtuoso(
                new NodeFactoryImpl(),
                new StatementFactoryImpl(),
                new QueryFactoryImpl(),
                new ResultFactoryImpl(),
                new StatementIteratorFactoryImpl(),
                $this->config['virtuosoConfig']
            );

        } else {
            $this->markTestSkipped('Array virtuosoConfig is not set in the test-config.yml.');
        }
    }

    public function tearDown()
    {
        if (null !== $this->fixture) {
            $this->fixture->dropGraph($this->testGraph);
        }

        parent::tearDown();
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
        $this->fixture->addStatements(array($stmtOne));
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
}
