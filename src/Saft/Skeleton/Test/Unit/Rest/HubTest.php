<?php

namespace Saft\Skeleton\Test\Unit\Rest;

use Saft\Data\NQuadsSerializerImpl;
use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\NodeUtils;
use Saft\Rdf\StatementFactoryImpl;
use Saft\Rdf\StatementIteratorFactoryImpl;
use Saft\Skeleton\Rest\Hub;
use Saft\Skeleton\Test\TestCase;
use Saft\Sparql\Query\QueryFactoryImpl;
use Saft\Store\BasicTriplePatternStore;
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

    // check for HTTP header Accept (invalid)
    public function testComputeRequestHeaderAcceptInvalid()
    {
        $fixture = new Hub(
            $this->getMockStore(),
            new StatementFactoryImpl(),
            new NodeFactoryImpl(),
            new NQuadsSerializerImpl('n-quads'),
            new NodeUtils()
        );

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
        $response = $fixture->computeRequest($request);

        $this->assertEquals(
            'Bad Request: Accept headers can not be empty.',
            $response->getBody()->__toString()
        );
        $this->assertEquals(400, $response->getStatusCode());
    }

    // check for HTTP header Accept (valid)
    public function testComputeRequestHeaderAcceptValid()
    {
        $fixture = new Hub(
            $this->getMockStore(),
            new StatementFactoryImpl(),
            new NodeFactoryImpl(),
            new NQuadsSerializerImpl('n-quads'),
            new NodeUtils()
        );

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
                'Accept' => 'application/n-triples,application/json'
            )
        );

        $this->assertEquals(200, $fixture->computeRequest($request)->getStatusCode());
    }

    // check for parameter action
    public function testComputeRequestParameterActionValid()
    {
        $fixture = new Hub(
            $this->getMockStore(),
            new StatementFactoryImpl(),
            new NodeFactoryImpl(),
            new NQuadsSerializerImpl('n-quads'),
            new NodeUtils()
        );

        // s, p and o must be set, otherwise we would get an error concerning missing s or p or o

        // check get
        $request = new ServerRequest(array('s' => '*', 'p' => '*', 'o' => '*', 'action' => 'get'));
        $this->assertEquals(200, $fixture->computeRequest($request)->getStatusCode());

        return;

        /*
         * TODO move the bottom code to the top, if the related function was implemented.
         */

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
    }

    // check for parameter action (invalid)
    public function testComputeRequestParameterActionInvalid()
    {
        $fixture = new Hub(
            $this->getMockStore(),
            new StatementFactoryImpl(),
            new NodeFactoryImpl(),
            new NQuadsSerializerImpl('n-quads'),
            new NodeUtils()
        );

        // s, p and o must be set, otherwise we would get an error concerning missing s or p or o

        $request = new ServerRequest(array('s' => '*', 'p' => '*', 'o' => '*', 'action' => 'something'));
        $response = $fixture->computeRequest($request);

        $this->assertEquals(
            'Bad Request: Parameter action must be one of these verbs: add, ask, count, delete, get',
            $response->getBody()->__toString()
        );
        $this->assertEquals(400, $response->getStatusCode());
    }

    // check for parameter case_insensitive (invalid)
    public function testComputeRequestParameterCaseInsensitiveInvalid()
    {
        $fixture = new Hub(
            $this->getMockStore(),
            new StatementFactoryImpl(),
            new NodeFactoryImpl(),
            new NQuadsSerializerImpl('n-quads'),
            new NodeUtils()
        );

        // s, p and o must be set, otherwise we would get an error concerning missing s or p or o

        $request = new ServerRequest(array('s' => '*', 'p' => '*', 'o' => '*', 'case_insensitive' => array()));
        $response = $fixture->computeRequest($request);

        $this->assertEquals(
            'Bad Request: Parameter case_insensitive must be one of these verbs: true, false',
            $response->getBody()->__toString()
        );
        $this->assertEquals(400, $response->getStatusCode());
    }

    // check for parameter case_insensitive (valid)
    public function testComputeRequestParameterCaseInsensitiveValid()
    {
        $fixture = new Hub(
            $this->getMockStore(),
            new StatementFactoryImpl(),
            new NodeFactoryImpl(),
            new NQuadsSerializerImpl('n-quads'),
            new NodeUtils()
        );

        // s, p and o must be set, otherwise we would get an error concerning missing s or p or o

        // check 'true'
        $request = new ServerRequest(array('s' => '*', 'p' => '*', 'o' => '*', 'case_insensitive' => 'true'));
        $this->assertEquals(200, $fixture->computeRequest($request)->getStatusCode());

        // check 'false'
        $request = new ServerRequest(array('s' => '*', 'p' => '*', 'o' => '*', 'case_insensitive' => 'false'));
        $this->assertEquals(200, $fixture->computeRequest($request)->getStatusCode());

        // check true
        $request = new ServerRequest(array('s' => '*', 'p' => '*', 'o' => '*', 'case_insensitive' => true));
        $this->assertEquals(200, $fixture->computeRequest($request)->getStatusCode());

        // check false
        $request = new ServerRequest(array('s' => '*', 'p' => '*', 'o' => '*', 'case_insensitive' => true));
        $this->assertEquals(200, $fixture->computeRequest($request)->getStatusCode());
    }

    // check for parameter graphUri (invalid)
    public function testComputeRequestParameterGraphUriInvalid()
    {
        // s, p and o must be set, otherwise we would get an error concerning missing s or p or o
        $request = new ServerRequest(
            array('s' => '*', 'p' => '*', 'o' => '*', 'graphUri' => 'invalid')
        );

        $fixture = new Hub(
            $this->getMockStore(),
            new StatementFactoryImpl(),
            new NodeFactoryImpl(),
            new NQuadsSerializerImpl('n-quads'),
            new NodeUtils()
        );
        $response = $fixture->computeRequest($request);

        $this->assertEquals(
            'Bad Request: Parameter graphUri must be an URI.',
            $response->getBody()->__toString()
        );
        $this->assertEquals(400, $response->getStatusCode());
    }

    // check for parameter graphUri (valid)
    public function testComputeRequestParameterGraphUriValid()
    {
        // s, p and o must be set, otherwise we would get an error concerning missing s or p or o
        $request = new ServerRequest(
            array('s' => '*', 'p' => '*', 'o' => '*', 'graphUri' => $this->testGraph->getUri())
        );

        $fixture = new Hub(
            $this->getMockStore(),
            new StatementFactoryImpl(),
            new NodeFactoryImpl(),
            new NQuadsSerializerImpl('n-quads'),
            new NodeUtils()
        );
        $response = $fixture->computeRequest($request);

        $this->assertEquals(200, $response->getStatusCode());
    }

    // check for parameter limit (lower 0)
    public function testComputeRequestParameterLimitLower0()
    {
        // s, p and o must be set, otherwise we would get an error concerning missing s or p or o
        $request = new ServerRequest(
            array('s' => '*', 'p' => '*', 'o' => '*', 'limit' => -1)
        );

        $fixture = new Hub(
            $this->getMockStore(),
            new StatementFactoryImpl(),
            new NodeFactoryImpl(),
            new NQuadsSerializerImpl('n-quads'),
            new NodeUtils()
        );
        $response = $fixture->computeRequest($request);

        $this->assertEquals(
            'Bad Request: Parameter limit is not equal or higher than 0.',
            $response->getBody()->__toString()
        );
        $this->assertEquals(400, $response->getStatusCode());
    }

    // check for parameter limit (if not integer)
    public function testComputeRequestParameterLimitNotInteger()
    {
        // s, p and o must be set, otherwise we would get an error concerning missing s or p or o
        $request = new ServerRequest(
            array('s' => '*', 'p' => '*', 'o' => '*', 'limit' => 'foo')
        );

        $fixture = new Hub(
            $this->getMockStore(),
            new StatementFactoryImpl(),
            new NodeFactoryImpl(),
            new NQuadsSerializerImpl('n-quads'),
            new NodeUtils()
        );
        $response = $fixture->computeRequest($request);

        $this->assertEquals(
            'Bad Request: Parameter limit is not an integer.',
            $response->getBody()->__toString()
        );
        $this->assertEquals(400, $response->getStatusCode());
    }

    // check for parameter o (invalid)
    public function testComputeRequestParameterOInvalid()
    {
        // s and p must be set, otherwise we would get an error concerning missing s or p
        $request = new ServerRequest(
            array('s' => '*', 'p' => '*', 'o' => 'invalid')
        );

        $fixture = new Hub(
            $this->getMockStore(),
            new StatementFactoryImpl(),
            new NodeFactoryImpl(),
            new NQuadsSerializerImpl('n-quads'),
            new NodeUtils()
        );
        $response = $fixture->computeRequest($request);

        $this->assertEquals(
            'Bad Request: Parameter o is invalid. Must be * or an URI.',
            $response->getBody()->__toString()
        );
        $this->assertEquals(400, $response->getStatusCode());
    }

    // check for parameter o (missing)
    public function testComputeRequestParameterOMissing()
    {
        // s and p must be set, otherwise we would get an error concerning missing s or p
        $request = new ServerRequest(
            array('s' => '*', 'p' => '*')
        );

        $fixture = new Hub(
            $this->getMockStore(),
            new StatementFactoryImpl(),
            new NodeFactoryImpl(),
            new NQuadsSerializerImpl('n-quads'),
            new NodeUtils()
        );
        $response = $fixture->computeRequest($request);

        $this->assertEquals(
            'Bad Request: Parameter o not set.',
            $response->getBody()->__toString()
        );
        $this->assertEquals(400, $response->getStatusCode());
    }

    // check for parameter offset (not integer)
    public function testComputeRequestParameterOffsetNotInteger()
    {
        // s, p and o must be set, otherwise we would get an error concerning missing s or p or o
        $request = new ServerRequest(
            array('s' => '*', 'p' => '*', 'o' => '*', 'offset' => 'not integer')
        );

        $fixture = new Hub(
            $this->getMockStore(),
            new StatementFactoryImpl(),
            new NodeFactoryImpl(),
            new NQuadsSerializerImpl('n-quads'),
            new NodeUtils()
        );
        $response = $fixture->computeRequest($request);

        $this->assertEquals(
            'Bad Request: Parameter offset is not an integer.',
            $response->getBody()->__toString()
        );
        $this->assertEquals(400, $response->getStatusCode());
    }

    // check for parameter offset (lower 1)
    public function testComputeRequestParameterOffsetLower1()
    {
        // s, p and o must be set, otherwise we would get an error concerning missing s or p or o
        $request = new ServerRequest(
            array('s' => '*', 'p' => '*', 'o' => '*', 'offset' => 0)
        );

        $fixture = new Hub(
            $this->getMockStore(),
            new StatementFactoryImpl(),
            new NodeFactoryImpl(),
            new NQuadsSerializerImpl('n-quads'),
            new NodeUtils()
        );
        $response = $fixture->computeRequest($request);

        $this->assertEquals(
            'Bad Request: Parameter offset is not equal or higher than 1.',
            $response->getBody()->__toString()
        );
        $this->assertEquals(400, $response->getStatusCode());
    }

    // check for parameter ot (invalid)
    public function testComputeRequestParameterOTInvalid()
    {
        // s, p and o are set. o is an URI, so ot must be set and literal or uri
        // we check if ot is literal or uri
        $request = new ServerRequest(
            array('s' => '*', 'p' => '*', 'o' => 'http://foo', 'ot' => 'neither uri nor literal')
        );

        $fixture = new Hub(
            $this->getMockStore(),
            new StatementFactoryImpl(),
            new NodeFactoryImpl(),
            new NQuadsSerializerImpl('n-quads'),
            new NodeUtils()
        );
        $response = $fixture->computeRequest($request);

        $this->assertEquals(
            'Bad Request: Parameter ot is neither uri nor literal.',
            $response->getBody()->__toString()
        );
        $this->assertEquals(400, $response->getStatusCode());
    }

    // check for parameter ot (missing)
    public function testComputeRequestParameterOTMissing()
    {
        // s, p and o are set. o is an URI, so ot must be set and literal or uri
        // we only check the case that ot is not set
        $request = new ServerRequest(
            array('s' => '*', 'p' => '*', 'o' => 'http://foo')
        );

        $fixture = new Hub(
            $this->getMockStore(),
            new StatementFactoryImpl(),
            new NodeFactoryImpl(),
            new NQuadsSerializerImpl('n-quads'),
            new NodeUtils()
        );
        $response = $fixture->computeRequest($request);

        $this->assertEquals(
            'Bad Request: Parameter o is an URI, so ot must be set.',
            $response->getBody()->__toString()
        );
        $this->assertEquals(400, $response->getStatusCode());
    }

    // check for parameter p (invalid)
    public function testComputeRequestParameterPInvalid()
    {
        // s must be set, otherwise we would get an error concerning missing s
        $request = new ServerRequest(
            array('s' => '*', 'p' => 'invalid')
        );

        $fixture = new Hub(
            $this->getMockStore(),
            new StatementFactoryImpl(),
            new NodeFactoryImpl(),
            new NQuadsSerializerImpl('n-quads'),
            new NodeUtils()
        );
        $response = $fixture->computeRequest($request);

        $this->assertEquals(
            'Bad Request: Parameter p is invalid. Must be * or an URI.',
            $response->getBody()->__toString()
        );
        $this->assertEquals(400, $response->getStatusCode());
    }

    // check for parameter p (missing)
    public function testComputeRequestParameterPMissing()
    {
        // s must be set, otherwise we would get an error concerning missing s
        $request = new ServerRequest(
            array('s' => '*')
        );

        $fixture = new Hub(
            $this->getMockStore(),
            new StatementFactoryImpl(),
            new NodeFactoryImpl(),
            new NQuadsSerializerImpl('n-quads'),
            new NodeUtils()
        );
        $response = $fixture->computeRequest($request);

        $this->assertEquals(
            'Bad Request: Parameter p not set.',
            $response->getBody()->__toString()
        );
        $this->assertEquals(400, $response->getStatusCode());
    }

    // check for parameter s (invalid)
    public function testComputeRequestParameterSInvalid()
    {
        $request = new ServerRequest(
            array('s' => 'invalid')
        );

        $fixture = new Hub(
            $this->getMockStore(),
            new StatementFactoryImpl(),
            new NodeFactoryImpl(),
            new NQuadsSerializerImpl('n-quads'),
            new NodeUtils()
        );
        $response = $fixture->computeRequest($request);

        $this->assertEquals(
            'Bad Request: Parameter s is invalid. Must be * or an URI.',
            $response->getBody()->__toString()
        );
        $this->assertEquals(400, $response->getStatusCode());
    }

    // check for parameter s (missing)
    public function testComputeRequestParameterSMissing()
    {
        $request = new ServerRequest();

        $fixture = new Hub(
            $this->getMockStore(),
            new StatementFactoryImpl(),
            new NodeFactoryImpl(),
            new NQuadsSerializerImpl('n-quads'),
            new NodeUtils()
        );
        $response = $fixture->computeRequest($request);

        $this->assertEquals(
            'Bad Request: Parameter s not set.',
            $response->getBody()->__toString()
        );
        $this->assertEquals(400, $response->getStatusCode());
    }

    // check for parameter reasoning_on (invalid)
    public function testComputeRequestParameterReasoningOnInvalid()
    {
        $fixture = new Hub(
            $this->getMockStore(),
            new StatementFactoryImpl(),
            new NodeFactoryImpl(),
            new NQuadsSerializerImpl('n-quads'),
            new NodeUtils()
        );

        // s, p and o must be set, otherwise we would get an error concerning missing s or p or o

        $request = new ServerRequest(array('s' => '*', 'p' => '*', 'o' => '*', 'reasoning_on' => array()));
        $response = $fixture->computeRequest($request);

        $this->assertEquals(
            'Bad Request: Parameter reasoning_on must be one of these verbs: true, false',
            $response->getBody()->__toString()
        );
        $this->assertEquals(400, $response->getStatusCode());
    }

    // check for parameter reasoning_on (valid)
    public function testComputeRequestParameterReasoningOnValid()
    {
        $fixture = new Hub(
            $this->getMockStore(),
            new StatementFactoryImpl(),
            new NodeFactoryImpl(),
            new NQuadsSerializerImpl('n-quads'),
            new NodeUtils()
        );

        // s, p and o must be set, otherwise we would get an error concerning missing s or p or o

        // check 'true'
        $request = new ServerRequest(array('s' => '*', 'p' => '*', 'o' => '*', 'reasoning_on' => 'true'));
        $this->assertEquals(200, $fixture->computeRequest($request)->getStatusCode());

        // check 'false'
        $request = new ServerRequest(array('s' => '*', 'p' => '*', 'o' => '*', 'reasoning_on' => 'false'));
        $this->assertEquals(200, $fixture->computeRequest($request)->getStatusCode());

        // check true
        $request = new ServerRequest(array('s' => '*', 'p' => '*', 'o' => '*', 'reasoning_on' => true));
        $this->assertEquals(200, $fixture->computeRequest($request)->getStatusCode());

        // check false
        $request = new ServerRequest(array('s' => '*', 'p' => '*', 'o' => '*', 'reasoning_on' => true));
        $this->assertEquals(200, $fixture->computeRequest($request)->getStatusCode());
    }

    // check that returned response object implement ResponseInterface
    public function testComputeRequestCheckResponseImplementsResponseInterface()
    {
        $request = new ServerRequest();

        $fixture = new Hub(
            $this->getMockStore(),
            new StatementFactoryImpl(),
            new NodeFactoryImpl(),
            new NQuadsSerializerImpl('n-quads'),
            new NodeUtils()
        );
        $response = $fixture->computeRequest($request);

        $this->assertClassOfInstanceImplements($response, 'Psr\Http\Message\ResponseInterface');
    }

    // check what happens, if method is not GET or POST
    public function testComputeRequestInvalidMethod()
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

        $fixture = new Hub(
            $this->getMockStore(),
            new StatementFactoryImpl(),
            new NodeFactoryImpl(),
            new NQuadsSerializerImpl('n-quads'),
            new NodeUtils()
        );
        $response = $fixture->computeRequest($request);

        $this->assertEquals('Method Not Allowed', $response->getReasonPhrase());
        $this->assertEquals(405, $response->getStatusCode());
    }

    // this tests the serialization of n-quads
    public function testComputeRequestSerializeNQuads()
    {
        $nodeFactory = new NodeFactoryImpl();
        $statementFactory = new StatementFactoryImpl();

        /*
         * fill mock store with test statements
         */
        $mockStore = $this->getMockStore();

        $mockStore->addStatements(array(
            $statementFactory->createStatement(
                $this->testGraph,
                $this->testGraph,
                $this->testGraph,
                $this->testGraph
            )
        ));

        // init hub
        $fixture = new Hub(
            $mockStore,
            new StatementFactoryImpl(),
            new NodeFactoryImpl(),
            new NQuadsSerializerImpl('n-triples'),
            new NodeUtils()
        );

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
                'Accept' => 'application/n-triples,application/json'
            )
        );

        $response = $fixture->computeRequest($request);

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertEquals(
            '<http://localhost/Saft/TestGraph/> <http://localhost/Saft/TestGraph/> '.
                '<http://localhost/Saft/TestGraph/> .' . PHP_EOL,
            $response->getBody()->getContents()
        );
    }
}
