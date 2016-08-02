<?php

namespace Saft\Addition\Rest\Test\Unit;

use Saft\Addition\Rest\Hub;
use Saft\Rdf\NodeUtils;
use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\StatementFactoryImpl;
use Saft\Rdf\StatementIteratorFactoryImpl;
use Saft\Sparql\Query\QueryFactoryImpl;
use Saft\Store\BasicTriplePatternStore;
use Saft\Test\TestCase;
use Zend\Diactoros\ServerRequest;

class HubTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->fixture = new Hub($this->getMockStore(), new NodeUtils());
    }

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

    // check for HTTP header Accept (invalid)
    public function testHandleRequestHeaderAcceptInvalid()
    {
        // s, p and o must be set, otherwise we would get an error concerning missing s or p or o

        $request = new ServerRequest(
            // server params
            array('s' => '*', 'p' => '*', 'o' => '*'),
            // uploaded files
            array(),
            // uri
            null,
            // method
            null,
            // body
            'php://input',
            // headers
            array(
                'Accept' => array()
            )
        );
        $response = $this->fixture->computeRequest($request);

        $this->assertEquals(
            'Bad Request: Accept headers can not be empty.',
            $response->getBody()->__toString()
        );
        $this->assertEquals(400, $response->getStatusCode());
    }

    // check for HTTP header Accept (valid)
    public function testHandleRequestHeaderAcceptValid()
    {
        // s, p and o must be set, otherwise we would get an error concerning missing s or p or o

        $request = new ServerRequest(
            // server params
            array('s' => '*', 'p' => '*', 'o' => '*'),
            // uploaded files
            array(),
            // uri
            null,
            // method
            null,
            // body
            'php://input',
            // headers
            array(
                'Accept' => 'application/json,application/n-triples'
            )
        );

        $this->assertEquals(200, $this->fixture->computeRequest($request)->getStatusCode());
    }

    // check for parameter action
    public function testHandleRequestParameterActionValid()
    {
        // s, p and o must be set, otherwise we would get an error concerning missing s or p or o

        // check add
        $request = new ServerRequest(array('s' => '*', 'p' => '*', 'o' => '*', 'action' => 'add'));
        $this->assertEquals(200, $this->fixture->computeRequest($request)->getStatusCode());

        // check ask
        $request = new ServerRequest(array('s' => '*', 'p' => '*', 'o' => '*', 'action' => 'ask'));
        $this->assertEquals(200, $this->fixture->computeRequest($request)->getStatusCode());

        // check count
        $request = new ServerRequest(array('s' => '*', 'p' => '*', 'o' => '*', 'action' => 'count'));
        $this->assertEquals(200, $this->fixture->computeRequest($request)->getStatusCode());

        // check delete
        $request = new ServerRequest(array('s' => '*', 'p' => '*', 'o' => '*', 'action' => 'delete'));
        $this->assertEquals(200, $this->fixture->computeRequest($request)->getStatusCode());

        // check get
        $request = new ServerRequest(array('s' => '*', 'p' => '*', 'o' => '*', 'action' => 'get'));
        $this->assertEquals(200, $this->fixture->computeRequest($request)->getStatusCode());
    }

    // check for parameter action (invalid)
    public function testHandleRequestParameterActionInvalid()
    {
        // s, p and o must be set, otherwise we would get an error concerning missing s or p or o

        $request = new ServerRequest(array('s' => '*', 'p' => '*', 'o' => '*', 'action' => 'something'));
        $response = $this->fixture->computeRequest($request);

        $this->assertEquals(
            'Bad Request: Parameter action must be one of these verbs: add, ask, count, delete, get',
            $response->getBody()->__toString()
        );
        $this->assertEquals(400, $response->getStatusCode());
    }

    // check for parameter case_insensitive (invalid)
    public function testHandleRequestParameterCaseInsensitiveInvalid()
    {
        // s, p and o must be set, otherwise we would get an error concerning missing s or p or o

        $request = new ServerRequest(array('s' => '*', 'p' => '*', 'o' => '*', 'case_insensitive' => array()));
        $response = $this->fixture->computeRequest($request);

        $this->assertEquals(
            'Bad Request: Parameter case_insensitive must be one of these verbs: true, false',
            $response->getBody()->__toString()
        );
        $this->assertEquals(400, $response->getStatusCode());
    }

    // check for parameter case_insensitive (valid)
    public function testHandleRequestParameterCaseInsensitiveValid()
    {
        // s, p and o must be set, otherwise we would get an error concerning missing s or p or o

        // check 'true'
        $request = new ServerRequest(array('s' => '*', 'p' => '*', 'o' => '*', 'case_insensitive' => 'true'));
        $this->assertEquals(200, $this->fixture->computeRequest($request)->getStatusCode());

        // check 'false'
        $request = new ServerRequest(array('s' => '*', 'p' => '*', 'o' => '*', 'case_insensitive' => 'false'));
        $this->assertEquals(200, $this->fixture->computeRequest($request)->getStatusCode());

        // check true
        $request = new ServerRequest(array('s' => '*', 'p' => '*', 'o' => '*', 'case_insensitive' => true));
        $this->assertEquals(200, $this->fixture->computeRequest($request)->getStatusCode());

        // check false
        $request = new ServerRequest(array('s' => '*', 'p' => '*', 'o' => '*', 'case_insensitive' => true));
        $this->assertEquals(200, $this->fixture->computeRequest($request)->getStatusCode());
    }

    // check for parameter graphUri (invalid)
    public function testHandleRequestParameterGraphUriInvalid()
    {
        // s, p and o must be set, otherwise we would get an error concerning missing s or p or o
        $request = new ServerRequest(
            array('s' => '*', 'p' => '*', 'o' => '*', 'graphUri' => 'invalid')
        );

        $response = $this->fixture->computeRequest($request);

        $this->assertEquals(
            'Bad Request: Parameter graphUri must be an URI.',
            $response->getBody()->__toString()
        );
        $this->assertEquals(400, $response->getStatusCode());
    }

    // check for parameter graphUri (valid)
    public function testHandleRequestParameterGraphUriValid()
    {
        // s, p and o must be set, otherwise we would get an error concerning missing s or p or o
        $request = new ServerRequest(
            array('s' => '*', 'p' => '*', 'o' => '*', 'graphUri' => $this->testGraph->getUri())
        );

        $response = $this->fixture->computeRequest($request);

        $this->assertEquals(200, $response->getStatusCode());
    }

    // check for parameter limit (lower 0)
    public function testHandleRequestParameterLimitLower0()
    {
        // s, p and o must be set, otherwise we would get an error concerning missing s or p or o
        $request = new ServerRequest(
            array('s' => '*', 'p' => '*', 'o' => '*', 'limit' => -1)
        );

        $response = $this->fixture->computeRequest($request);

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

        $response = $this->fixture->computeRequest($request);

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

        $response = $this->fixture->computeRequest($request);

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

        $response = $this->fixture->computeRequest($request);

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

        $response = $this->fixture->computeRequest($request);

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

        $response = $this->fixture->computeRequest($request);

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

        $response = $this->fixture->computeRequest($request);

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

        $response = $this->fixture->computeRequest($request);

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

        $response = $this->fixture->computeRequest($request);

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

        $response = $this->fixture->computeRequest($request);

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

        $response = $this->fixture->computeRequest($request);

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

        $response = $this->fixture->computeRequest($request);

        $this->assertEquals(
            'Bad Request: Parameter s not set.',
            $response->getBody()->__toString()
        );
        $this->assertEquals(400, $response->getStatusCode());
    }

    // check for parameter reasoning_on (invalid)
    public function testHandleRequestParameterReasoningOnInvalid()
    {
        // s, p and o must be set, otherwise we would get an error concerning missing s or p or o

        $request = new ServerRequest(array('s' => '*', 'p' => '*', 'o' => '*', 'reasoning_on' => array()));
        $response = $this->fixture->computeRequest($request);

        $this->assertEquals(
            'Bad Request: Parameter reasoning_on must be one of these verbs: true, false',
            $response->getBody()->__toString()
        );
        $this->assertEquals(400, $response->getStatusCode());
    }

    // check for parameter reasoning_on (valid)
    public function testHandleRequestParameterReasoningOnValid()
    {
        // s, p and o must be set, otherwise we would get an error concerning missing s or p or o

        // check 'true'
        $request = new ServerRequest(array('s' => '*', 'p' => '*', 'o' => '*', 'reasoning_on' => 'true'));
        $this->assertEquals(200, $this->fixture->computeRequest($request)->getStatusCode());

        // check 'false'
        $request = new ServerRequest(array('s' => '*', 'p' => '*', 'o' => '*', 'reasoning_on' => 'false'));
        $this->assertEquals(200, $this->fixture->computeRequest($request)->getStatusCode());

        // check true
        $request = new ServerRequest(array('s' => '*', 'p' => '*', 'o' => '*', 'reasoning_on' => true));
        $this->assertEquals(200, $this->fixture->computeRequest($request)->getStatusCode());

        // check false
        $request = new ServerRequest(array('s' => '*', 'p' => '*', 'o' => '*', 'reasoning_on' => true));
        $this->assertEquals(200, $this->fixture->computeRequest($request)->getStatusCode());
    }

    // check that returned response object implement ResponseInterface
    public function testHandleRequestCheckResponseImplementsResponseInterface()
    {
        $request = new ServerRequest();

        $response = $this->fixture->computeRequest($request);

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
        $request = new ServerRequest(array(), array(), null, 'DELETE');

        $response = $this->fixture->computeRequest($request);

        $this->assertEquals('Method Not Allowed', $response->getReasonPhrase());
        $this->assertEquals(405, $response->getStatusCode());
    }
}
