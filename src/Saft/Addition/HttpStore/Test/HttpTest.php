<?php

namespace Saft\Addition\HttpStore\Test;

use Saft\Addition\HttpStore\Store\Http;
use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\StatementFactoryImpl;
use Saft\Rdf\StatementIteratorFactoryImpl;
use Saft\Sparql\Query\QueryFactoryImpl;
use Saft\Store\Result\ResultFactoryImpl;
use Saft\Store\Test\StoreAbstractTest;

class HttpTest extends StoreAbstractTest
{
    public function setUp()
    {
        parent::setUp();

        $rights = array();

        /*
         * Load configuration
         */
        if (true === isset($this->config['httpConfig'])) {
            $this->fixture = new Http(
                new NodeFactoryImpl(),
                new StatementFactoryImpl(),
                new QueryFactoryImpl(),
                new ResultFactoryImpl(),
                new StatementIteratorFactoryImpl(),
                $this->config['httpConfig']
            );

            $rights = $this->fixture->getRights();

        } else {
            $this->markTestSkipped('Array httpConfig is not set in the test-config.yml.');
        }

        /*
         * Skip test, if you dont have enough rights.
         */
        if (false === $rights['graphUpdate']) {
            $this->markTestSkipped('Test skipped, because the adapter can not create/drop graphs.');
        }

        if (false === $rights['tripleQuerying']) {
            $this->markTestSkipped('Test skipped, because the adapter can not query triples.');
        }

        if (false === $rights['tripleUpdate']) {
            $this->markTestSkipped('Test skipped, because the adapter can not update triples.');
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
        new Http(
            new NodeFactoryImpl(),
            new StatementFactoryImpl(),
            new QueryFactoryImpl(),
            new ResultFactoryImpl(),
            new StatementIteratorFactoryImpl(),
            $config
        );
    }

    public function testOpenConnectionInvalidQueryUrl()
    {
        // We expect that openConnection fails, because the query URL is not valid
        $this->setExpectedException('\Exception');

        $config = array('queryUrl' => 'http://not existend');
        new Http(
            new NodeFactoryImpl(),
            new StatementFactoryImpl(),
            new QueryFactoryImpl(),
            new ResultFactoryImpl(),
            new StatementIteratorFactoryImpl(),
            $config
        );
    }

    /*
     * Tests for getRights
     */

    // this test depends on that dbpedia DOES NOT give you SPARQL UPDATE rights to create/drop graphs or triples.
    public function testGetRights()
    {
        $config = array('queryUrl' => 'http://dbpedia.org/sparql');

        $this->fixture = new Http(
            new NodeFactoryImpl(),
            new StatementFactoryImpl(),
            new QueryFactoryImpl(),
            new ResultFactoryImpl(),
            new StatementIteratorFactoryImpl(),
            $config
        );

        $this->assertEquals(
            array(
                'graphUpdate' => false,
                'tripleQuerying' => true,
                'tripleUpdate' => false,
            ),
            $this->fixture->getRights()
        );
    }
}
