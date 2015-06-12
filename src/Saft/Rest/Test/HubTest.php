<?php

namespace Saft\Rest\Test;

use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\StatementFactoryImpl;
use Saft\Rdf\StatementIteratorFactoryImpl;
use Saft\Rest\Hub;
use Saft\Sparql\Query\QueryFactoryImpl;
use Saft\Store\Test\BasicTriplePatternStore;
use Saft\Test\TestCase;
use Zend\Diactoros\ServerRequest;

class HubTest extends TestCase
{
    /**
     * @return Store Returns a basic store implementation which emulates a basic tripple store.
     */
    protected function getMockStore()
    {
        return new BasicTriplePatternStore(
            new NodeFactoryImpl(),
            new StatementFactoryImpl(),
            new QueryFactoryImpl(),
            new StatementIteratorFactoryImpl()
        );
    }

    /*
     * Tests for handleRequest
     */

    public function testHandleRequestParameterSMissing()
    {
        $request = new ServerRequest();

        $fixture = new Hub($this->getMockStore());
        $response = $fixture->computeRequest($request);

        $this->assertEquals(
            'Bad Request: Parameter s not set.',
            $response->getBody()->__toString()
        );
        $this->assertEquals(400, $response->getStatusCode());
    }

    // check that returned response object implement ResponseInterface
    public function testHandleRequestCheckResponseImplementsResponseInterface()
    {
        $request = new ServerRequest();

        $fixture = new Hub($this->getMockStore());
        $response = $fixture->computeRequest($request);

        $this->assertClassOfInstanceImplements($response, 'Psr\Http\Message\ResponseInterface');
    }

    // check what happens, if method is not GET or POST
    public function testHandleRequestInvalidMethod()
    {
        /*
         * Parameter list for ServerRequest:
         *  array $serverParams = [],
         *  array $uploadedFiles = [],
         *  $uri = null,
         *  $method = null,
         *  $body = 'php://input',
         *  array $headers = []
         */
        $request = new ServerRequest(
            array(),
            array(),
            null,
            'DELETE'
        );

        $fixture = new Hub($this->getMockStore());
        $response = $fixture->computeRequest($request);

        $this->assertEquals('Method Not Allowed', $response->getReasonPhrase());
        $this->assertEquals(405, $response->getStatusCode());
    }
}
