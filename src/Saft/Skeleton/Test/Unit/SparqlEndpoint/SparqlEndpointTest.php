<?php

namespace Saft\Skeleton\Test\Unit\SparqlEndpoint;

use Saft\Skeleton\Data\SerializerFactory;
use Saft\Skeleton\SparqlEndpoint\SparqlEndpoint;
use Saft\Skeleton\Test\TestCase;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\StatementImpl;
use Saft\Rdf\StatementFactoryImpl;
use Saft\Rdf\StatementIteratorFactoryImpl;
use Saft\Sparql\Query\QueryFactoryImpl;
use Saft\Sparql\Query\QueryUtils;
use Saft\Store\BasicTriplePatternStore;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SparqlEndpointTest extends TestCase
{
    /**
     * @var Store
     */
    protected $store;

    public function setUp()
    {
        parent::setUp();

        // store
        $this->store = new BasicTriplePatternStore(
            new NodeFactoryImpl(),
            new StatementFactoryImpl(),
            new QueryFactoryImpl(),
            new StatementIteratorFactoryImpl()
        );

        $serializerFactory = new SerializerFactory(new NodeFactoryImpl(), new StatementFactoryImpl());

        $this->fixture = new SparqlEndpoint($this->store, $serializerFactory, new QueryUtils());
    }

    // test GET request with query parameter
    public function testHandleRequestQueryGET()
    {
        // add test data to graph
        $this->store->addStatements(
            array(
                new StatementImpl(
                    new NamedNodeImpl('http://s'),
                    new NamedNodeImpl('http://p'),
                    new NamedNodeImpl('http://o')
                )
            ),
            $this->testGraph
        );

        /*
         * request
         */
        $request = Request::create(
            '/',
            'GET',
            array(
                'query' => 'PREFIX%20dc%3A%20%3Chttp%3A%2F%2Fpurl.org%2Fdc%2Felements' .
                           '%2F1.1%2F%3E%20%0ASELECT%20%3Fs%20%3Fp%20%3Fo%20%0AWHERE' .
                           '%20%7B%20%3Fs%20%3Fp%20%3Fo.%20%7D'
            )
        );
        $request->headers->set('Content-Type', 'application/json');
        $request->headers->set('User-agent', 'my-sparql-client/0.1');

        /*
         * response
         */
        $expectedResponse = new Response(
            '',
            Response::HTTP_OK,
            array(
                'Content-Type' => 'application/x-turtle'
            )
        );

        $response = $this->fixture->handleRequest($request);
        $this->assertEquals($response, $expectedResponse);
    }

    // test GET request with query parameter and accept headers
    public function testHandleRequestGETWithQueryAcceptHeaders()
    {
        // add test data to graph
        $this->store->addStatements(
            array(
                new StatementImpl(
                    new NamedNodeImpl('http://s'),
                    new NamedNodeImpl('http://p'),
                    new NamedNodeImpl('http://o')
                )
            ),
            $this->testGraph
        );

        /*
         * request
         */
        $request = Request::create(
            '/',
            'GET',
            array(
                'query' => 'PREFIX%20dc%3A%20%3Chttp%3A%2F%2Fpurl.org%2Fdc%2Felements' .
                           '%2F1.1%2F%3E%20%0ASELECT%20%3Fs%20%3Fp%20%3Fo%20%0AWHERE' .
                           '%20%7B%20%3Fs%20%3Fp%20%3Fo.%20%7D'
            )
        );
        $request->headers->set('Accept', 'application/rdf+xml, application/x-turtle');
        $request->headers->set('Content-Type', 'application/json');
        $request->headers->set('User-agent', 'my-sparql-client/0.1');

        /*
         * response
         */
        $expectedResponse = new Response(
            '',
            Response::HTTP_OK,
            array(
                'Content-Type' => 'application/x-turtle'
            )
        );

        $response = $this->fixture->handleRequest($request);
        $this->assertEquals($response, $expectedResponse);
    }

    // test GET request with no query parameter
    public function testHandleRequestGETButNoQueryParameter()
    {
        /*
         * request
         */
        $request = Request::create(
            '/',
            'GET',
            array()
        );

        /*
         * response
         */
        $expectedResponse = new Response(
            Response::$statusTexts[500],
            Response::HTTP_INTERNAL_SERVER_ERROR,
            array(
                'Content-Type' => 'application/x-turtle'
            )
        );

        $response = $this->fixture->handleRequest($request);
        $this->assertEquals($response, $expectedResponse);
    }
}
