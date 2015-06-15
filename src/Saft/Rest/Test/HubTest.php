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

    // check for parameter action
    public function testHandleRequestParameterActionValid()
    {
        $fixture = new Hub($this->getMockStore());

        // s, p and o must be set, otherwise we would get an error concerning missing s or p or o

        // check add
        $request = new ServerRequest(array('s' => '*', 'p' => '*', 'o' => '*', 'action' => 'add'));
        $this->assertEquals(200, $fixture->computeRequest($request)->getStatusCode());

        // check ask
        $request = new ServerRequest(array('s' => '*', 'p' => '*', 'o' => '*', 'action' => 'ask'));
        $this->assertEquals(200, $fixture->computeRequest($request)->getStatusCode());

        // check count
        $request = new ServerRequest(array('s' => '*', 'p' => '*', 'o' => '*', 'action' => 'count'));
        $this->assertEquals(200, $fixture->computeRequest($request)->getStatusCode());

        // check delete
        $request = new ServerRequest(array('s' => '*', 'p' => '*', 'o' => '*', 'action' => 'delete'));
        $this->assertEquals(200, $fixture->computeRequest($request)->getStatusCode());

        // check get
        $request = new ServerRequest(array('s' => '*', 'p' => '*', 'o' => '*', 'action' => 'get'));
        $this->assertEquals(200, $fixture->computeRequest($request)->getStatusCode());
    }

    // check for parameter action (invalid)
    public function testHandleRequestParameterActionInvalid()
    {
        $fixture = new Hub($this->getMockStore());

        // s, p and o must be set, otherwise we would get an error concerning missing s or p or o

        $request = new ServerRequest(array('s' => '*', 'p' => '*', 'o' => '*', 'action' => 'something'));
        $response = $fixture->computeRequest($request);

        $this->assertEquals(
            'Bad Request: Parameter action must be one of these verbs: add, ask, count, delete, get',
            $response->getBody()->__toString()
        );
        $this->assertEquals(400, $response->getStatusCode());
    }

    // check for parameter limit (lower 0)
    public function testHandleRequestParameterLimitLower0()
    {
        // s, p and o must be set, otherwise we would get an error concerning missing s or p or o
        $request = new ServerRequest(
            array('s' => '*', 'p' => '*', 'o' => '*', 'limit' => -1)
        );

        $fixture = new Hub($this->getMockStore());
        $response = $fixture->computeRequest($request);

        $this->assertEquals(
            'Bad Request: Parameter limit is not equal or higher than 0.',
            $response->getBody()->__toString()
        );
        $this->assertEquals(400, $response->getStatusCode());
    }

    // check for parameter limit (if not integer)
    public function testHandleRequestParameterLimitNotInteger()
    {
        // s, p and o must be set, otherwise we would get an error concerning missing s or p or o
        $request = new ServerRequest(
            array('s' => '*', 'p' => '*', 'o' => '*', 'limit' => 'foo')
        );

        $fixture = new Hub($this->getMockStore());
        $response = $fixture->computeRequest($request);

        $this->assertEquals(
            'Bad Request: Parameter limit is not an integer.',
            $response->getBody()->__toString()
        );
        $this->assertEquals(400, $response->getStatusCode());
    }

    // check for parameter o (invalid)
    public function testHandleRequestParameterOInvalid()
    {
        // s and p must be set, otherwise we would get an error concerning missing s or p
        $request = new ServerRequest(
            array('s' => '*', 'p' => '*', 'o' => 'invalid')
        );

        $fixture = new Hub($this->getMockStore());
        $response = $fixture->computeRequest($request);

        $this->assertEquals(
            'Bad Request: Parameter o is invalid. Must be * or an URI.',
            $response->getBody()->__toString()
        );
        $this->assertEquals(400, $response->getStatusCode());
    }

    // check for parameter o (missing)
    public function testHandleRequestParameterOMissing()
    {
        // s and p must be set, otherwise we would get an error concerning missing s or p
        $request = new ServerRequest(
            array('s' => '*', 'p' => '*')
        );

        $fixture = new Hub($this->getMockStore());
        $response = $fixture->computeRequest($request);

        $this->assertEquals(
            'Bad Request: Parameter o not set.',
            $response->getBody()->__toString()
        );
        $this->assertEquals(400, $response->getStatusCode());
    }

    // check for parameter offset (not integer)
    public function testHandleRequestParameterOffsetNotInteger()
    {
        // s, p and o must be set, otherwise we would get an error concerning missing s or p or o
        $request = new ServerRequest(
            array('s' => '*', 'p' => '*', 'o' => '*', 'offset' => 'not integer')
        );

        $fixture = new Hub($this->getMockStore());
        $response = $fixture->computeRequest($request);

        $this->assertEquals(
            'Bad Request: Parameter offset is not an integer.',
            $response->getBody()->__toString()
        );
        $this->assertEquals(400, $response->getStatusCode());
    }

    // check for parameter offset (lower 1)
    public function testHandleRequestParameterOffsetLower1()
    {
        // s, p and o must be set, otherwise we would get an error concerning missing s or p or o
        $request = new ServerRequest(
            array('s' => '*', 'p' => '*', 'o' => '*', 'offset' => 0)
        );

        $fixture = new Hub($this->getMockStore());
        $response = $fixture->computeRequest($request);

        $this->assertEquals(
            'Bad Request: Parameter offset is not equal or higher than 1.',
            $response->getBody()->__toString()
        );
        $this->assertEquals(400, $response->getStatusCode());
    }

    // check for parameter ot (invalid)
    public function testHandleRequestParameterOTInvalid()
    {
        // s, p and o are set. o is an URI, so ot must be set and literal or uri
        // we check if ot is literal or uri
        $request = new ServerRequest(
            array('s' => '*', 'p' => '*', 'o' => 'http://foo', 'ot' => 'neither uri nor literal')
        );

        $fixture = new Hub($this->getMockStore());
        $response = $fixture->computeRequest($request);

        $this->assertEquals(
            'Bad Request: Parameter ot is neither uri nor literal.',
            $response->getBody()->__toString()
        );
        $this->assertEquals(400, $response->getStatusCode());
    }

    // check for parameter ot (missing)
    public function testHandleRequestParameterOTMissing()
    {
        // s, p and o are set. o is an URI, so ot must be set and literal or uri
        // we only check the case that ot is not set
        $request = new ServerRequest(
            array('s' => '*', 'p' => '*', 'o' => 'http://foo')
        );

        $fixture = new Hub($this->getMockStore());
        $response = $fixture->computeRequest($request);

        $this->assertEquals(
            'Bad Request: Parameter o is an URI, so ot must be set.',
            $response->getBody()->__toString()
        );
        $this->assertEquals(400, $response->getStatusCode());
    }

    // check for parameter p (invalid)
    public function testHandleRequestParameterPInvalid()
    {
        // s must be set, otherwise we would get an error concerning missing s
        $request = new ServerRequest(
            array('s' => '*', 'p' => 'invalid')
        );

        $fixture = new Hub($this->getMockStore());
        $response = $fixture->computeRequest($request);

        $this->assertEquals(
            'Bad Request: Parameter p is invalid. Must be * or an URI.',
            $response->getBody()->__toString()
        );
        $this->assertEquals(400, $response->getStatusCode());
    }

    // check for parameter p (missing)
    public function testHandleRequestParameterPMissing()
    {
        // s must be set, otherwise we would get an error concerning missing s
        $request = new ServerRequest(
            array('s' => '*')
        );

        $fixture = new Hub($this->getMockStore());
        $response = $fixture->computeRequest($request);

        $this->assertEquals(
            'Bad Request: Parameter p not set.',
            $response->getBody()->__toString()
        );
        $this->assertEquals(400, $response->getStatusCode());
    }

    // check for parameter s (invalid)
    public function testHandleRequestParameterSInvalid()
    {
        $request = new ServerRequest(
            array('s' => 'invalid')
        );

        $fixture = new Hub($this->getMockStore());
        $response = $fixture->computeRequest($request);

        $this->assertEquals(
            'Bad Request: Parameter s is invalid. Must be * or an URI.',
            $response->getBody()->__toString()
        );
        $this->assertEquals(400, $response->getStatusCode());
    }

    // check for parameter s (missing)
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
