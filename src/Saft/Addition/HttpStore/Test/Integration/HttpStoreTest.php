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

        /*
         * first check, if target server is online
         */
        $curl = new Curl();
        $curl->get($config['query-url']);
        if ($curl->error) {
            $this->markTestSkipped('Query URL '.$config['query-url'].' is not reachable: '. $curl->errorMessage);
        }

        // init fixture
        $this->fixture = new HttpStore(
            $this->nodeFactory,
            $this->statementFactory,
            new ResultFactoryImpl(),
            $this->statementIteratorFactory,
            $this->rdfHelpers,
            $config
        );
        $this->fixture->setClient($curl);
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
