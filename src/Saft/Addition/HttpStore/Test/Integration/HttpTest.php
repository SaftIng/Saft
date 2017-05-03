<?php

namespace Saft\Addition\HttpStore\Test\Integration;

use Curl\Curl;
use Saft\Addition\HttpStore\Store\Http;
use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\NodeUtils;
use Saft\Rdf\StatementFactoryImpl;
use Saft\Rdf\StatementIteratorFactoryImpl;
use Saft\Sparql\SparqlUtils;
use Saft\Sparql\Query\QueryFactoryImpl;
use Saft\Sparql\Query\QueryUtils;
use Saft\Sparql\Result\ResultFactoryImpl;
use Saft\Store\Test\StoreAbstractTest;

class HttpTest extends StoreAbstractTest
{
    public function setUp()
    {
        parent::setUp();

        $this->loadTestConfiguration(__DIR__ .'/../../test-config.yml');

        /*
         * first check, if target server is online
         */
        $curl = new Curl();
        $curl->get($this->configuration['httpConfig']['queryUrl']);
        if (false == $curl->response) {
            $this->markTestSkipped(
                'Query URL ' . $this->configuration['httpConfig']['queryUrl'] . ' is not reachable. '
                . 'Ignore integration test.'
            );
        }

        $rights = array();

        /*
         * Load configuration
         */
        if (true === isset($this->configuration['httpConfig'])) {
            $this->fixture = new Http(
                new NodeFactoryImpl(new NodeUtils()),
                new StatementFactoryImpl(),
                new QueryFactoryImpl(new NodeUtils(), new QueryUtils()),
                new ResultFactoryImpl(),
                new StatementIteratorFactoryImpl(),
                new NodeUtils(),
                new QueryUtils(),
                new SparqlUtils(new StatementIteratorFactoryImpl()),
                $this->configuration['httpConfig']
            );
            $this->fixture->setClient(new Curl());

            $rights = $this->fixture->getRights();

        } else {
            $this->markTestSkipped('Array httpConfig is not set in the test-config.yml.');
        }

        /*
         * Skip test, if you dont have enough rights.
         */
        if (false === $rights['graphUpdate']) {
            $this->markTestSkipped(
                'Test skipped, because the adapter can not create/drop graphs. ' .
                'Has the webuser of the SPARQL endpoint (Virtuoso?) enough rights?'
            );
        }

        if (false === $rights['tripleQuerying']) {
            $this->markTestSkipped(
                'Test skipped, because the adapter can not query triples. ' .
                'Has the webuser of the SPARQL endpoint (Virtuoso?) enough rights?'
            );
        }

        if (false === $rights['tripleUpdate']) {
            $this->markTestSkipped(
                'Test skipped, because the adapter can not update triples. ' .
                'Has the webuser of the SPARQL endpoint (Virtuoso?) enough rights?'
            );
        }

        $this->fixture->dropGraph($this->testGraph);
        $this->fixture->createGraph($this->testGraph);
    }

    /*
     * Tests to check add and delete statements on default graph.
     */

    // override test from parent class because we dont know if the target server supports
    // write access to default graph and it may throw an exception, if not. because of that
    // we just dont test it to avoid confusion.
    public function testAddAndDeleteStatementsOnDefaultGraph()
    {
        $this->markTestSkipped(
            'Skip test from parent class because we dont know if the target server supports '.
            'write access to default graph and it may throw an exception, if not. because of that '.
            'we just dont test it to avoid confusion.'
        );
    }

    /*
     * Tests for openConnection
     */

    public function testOpenConnectionInvalidAuthUrl()
    {
        // We expect that authentication fails, because the auth url is not valid
        $this->setExpectedException('\Exception');

        $config = array('authUrl' => 'http://not existend');
        $http = new Http(
            new NodeFactoryImpl(new NodeUtils()),
            new StatementFactoryImpl(),
            new QueryFactoryImpl(new NodeUtils(), new QueryUtils()),
            new ResultFactoryImpl(),
            new StatementIteratorFactoryImpl(),
            new NodeUtils(),
            new QueryUtils(),
            new SparqlUtils(new StatementIteratorFactoryImpl()),
            $config
        );
        $http->setClient(new Curl());
        $http->openConnection();
    }

    public function testOpenConnectionInvalidQueryUrl()
    {
        // We expect that openConnection fails, because the query URL is not valid
        $this->setExpectedException('\Exception');

        $config = array('queryUrl' => 'http://not existend');
        $http = new Http(
            new NodeFactoryImpl(new NodeUtils()),
            new StatementFactoryImpl(),
            new QueryFactoryImpl(new NodeUtils(), new QueryUtils()),
            new ResultFactoryImpl(),
            new StatementIteratorFactoryImpl(),
            new NodeUtils(),
            new QueryUtils(),
            new SparqlUtils(new StatementIteratorFactoryImpl()),
            $config
        );
        $http->setClient(new Curl());
        $http->openConnection();
    }

    /*
     * Tests for getRights
     */

    // this test depends on that dbpedia DOES NOT give you SPARQL UPDATE rights to create/drop graphs or triples.
    public function testGetRights()
    {
        $config = array('queryUrl' => 'http://dbpedia.org/sparql');

        $fixture = new Http(
            new NodeFactoryImpl(new NodeUtils()),
            new StatementFactoryImpl(),
            new QueryFactoryImpl(new NodeUtils(), new QueryUtils()),
            new ResultFactoryImpl(),
            new StatementIteratorFactoryImpl(),
            new NodeUtils(),
            new QueryUtils(),
            new SparqlUtils(new StatementIteratorFactoryImpl()),
            $config
        );
        $fixture->setClient(new Curl());
        $fixture->openConnection();

        $this->assertEquals(
            array(
                'graphUpdate' => false,
                'tripleQuerying' => true,
                'tripleUpdate' => false,
            ),
            $fixture->getRights()
        );
    }

    /*
     * Tests for query
     */

    // override test from parent class because Virtuoso does not support what we want to test.
    public function testQueryAddAndQueryStatementsDefaultGraph()
    {
        // See: https://github.com/openlink/virtuoso-opensource/issues/417
        $this->markTestSkipped(
            'Skip test from parent class because we dont know if the target server supports '.
            'write access to default graph and it may throw an exception, if not. because of that '.
            'we just dont test it to avoid confusion.'
        );
    }
}
