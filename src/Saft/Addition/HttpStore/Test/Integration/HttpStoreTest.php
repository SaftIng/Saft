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

namespace Saft\Addition\HttpStore\Test\Integration;

use Curl\Curl;
use Saft\Addition\HttpStore\Store\HttpStore;
use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\RdfHelpers;
use Saft\Rdf\StatementFactoryImpl;
use Saft\Rdf\StatementIteratorFactoryImpl;
use Saft\Rdf\Test\TestCase;
use Saft\Sparql\Result\ResultFactoryImpl;
use Saft\Sparql\Result\SetResult;
use Saft\Sparql\Result\ValueResult;
use Symfony\Component\Yaml\Yaml;

class HttpStoreTest extends TestCase
{
    public function setUp()
    {
        global $config;

        parent::setUp();

        $this->setInstance();
    }

    protected function setInstance(array $customConfig = null)
    {
        if (null == $customConfig) {
            global $config;
        } else {
            $config = $customConfig;
        }

        /*
         * first check, if target server is online
         */
        $curl = new Curl();

        // init fixture
        $this->fixture = new HttpStore(
            $this->nodeFactory,
            $this->statementFactory,
            new ResultFactoryImpl(),
            $this->statementIteratorFactory,
            $this->rdfHelpers,
            $config,
            $curl
        );
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage HTTP/1.1 401 Unauthorized
     */
    public function testAuthNoAccess()
    {
        $this->setInstance([
            'query-url' => 'http://virtuoso-auth-required:8890/sparql'
        ]);

        // will throw an exception cause no write access
        $this->fixture->addStatements([
            $this->statementFactory->createStatement(
                $this->nodeFactory->createNamedNode('http://a'),
                $this->nodeFactory->createNamedNode('http://b'),
                $this->nodeFactory->createNamedNode('http://c'),
                $this->testGraph
            )
        ]);
    }

    /**
     * Requires a certain user on the target server to test auth.
     */
    public function testAuthOnServer()
    {
        if (isset($_ENV['TRAVIS'])) {
            $this->markTestSkipped('We need a pre-configured Virtuoso server for this test. Test only runs locally.');
        }

        $this->setInstance([
            'query-url' => 'http://virtuoso-auth-required:8890/sparql',
            'username' => 'test1',
            'password' => 'test1',
        ]);

        // will throw an exception cause no write access
        $this->fixture->dropGraph($this->testGraph);
        $this->fixture->createGraph($this->testGraph);

        // check that graph is empty
        $result = $this->fixture->query('ASK FROM <'.$this->testGraph.'> WHERE {?s ?p ?o.}');
        $this->assertFalse($result->getValue());

        // add 1 triple
        $this->fixture->addStatements([
            $this->statementFactory->createStatement(
                $this->nodeFactory->createNamedNode('http://a'),
                $this->nodeFactory->createNamedNode('http://b'),
                $this->nodeFactory->createNamedNode('http://c'),
                $this->testGraph
            )
        ]);

        // check that triple was created
        $result = $this->fixture->query('ASK FROM <'.$this->testGraph.'> WHERE {?s ?p ?o.}');
        $this->assertTrue($result->getValue());
    }

    public function testQueryAsk()
    {
        $this->fixture->addStatements([
            $this->statementFactory->createStatement(
                $this->nodeFactory->createNamedNode('http://a'),
                $this->nodeFactory->createNamedNode('http://b'),
                $this->nodeFactory->createNamedNode('http://c'),
                $this->testGraph
            )
        ]);

        $result = $this->fixture->query('ASK FROM <'.$this->testGraph.'> WHERE {?s ?p ?o.}');

        $this->assertTrue($result instanceof ValueResult);
        $this->assertEquals(true, $result->getValue());
    }

    public function testQuery()
    {
        $this->fixture->addStatements([
            $this->statementFactory->createStatement(
                $this->nodeFactory->createNamedNode('http://a'),
                $this->nodeFactory->createNamedNode('http://b'),
                $this->nodeFactory->createNamedNode('http://c'),
                $this->testGraph
            )
        ]);

        $result = $this->fixture->query('SELECT * FROM <'.$this->testGraph.'> WHERE {?s ?p ?o.}');

        $this->assertTrue($result instanceof SetResult);
        $this->assertEquals(1, \count($result));

        $this->assertEquals(
            [
                's' => $this->nodeFactory->createNamedNode('http://a'),
                'p' => $this->nodeFactory->createNamedNode('http://b'),
                'o' => $this->nodeFactory->createNamedNode('http://c'),
            ],
            $result[0]
        );
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Virtuoso 37000 Error SP030: SPARQL compiler, line 1: syntax error at 'invalid' before '}' SPARQL query: define sql:big-data-const 0 SELECT * FROM <http://localhost/Saft/TestGraph/> WHERE {?s ?p ?o. invalid
     */
    public function testQueryInvalidSelectQuery()
    {
        $result = $this->fixture->query('SELECT * FROM <'.$this->testGraph.'> WHERE {?s ?p ?o. invalid');
    }
}
