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
