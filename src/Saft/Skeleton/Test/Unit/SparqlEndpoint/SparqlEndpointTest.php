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
    public function testHandleRequestQueryGETSelectQuery()
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
            '@prefix rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> .
@prefix xsd: <http://www.w3.org/2001/XMLSchema#> .

[]
  a <http://www.w3.org/2005/sparql-results#ResultSet> ;
  rdf:resultVariable "s"^^xsd:string, "p"^^xsd:string, "o"^^xsd:string ;
  rdf:solution [ rdf:binding [
      rdf:variable "s"^^xsd:string ;
      rdf:value <http://s>
    ], [
      rdf:variable "p"^^xsd:string ;
      rdf:value <http://p>
    ], [
      rdf:variable "o"^^xsd:string ;
      rdf:value <http://o>
    ] ] .'  ,
            Response::HTTP_OK,
            array(
                'Content-Type' => 'application/x-turtle'
            )
        );

        $response = $this->fixture->handleRequest($request);
        $this->assertEquals($expectedResponse, $response);
    }

    // test GET request with query parameter
    public function testHandleRequestQueryGETAskQuery()
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
                'query' => 'ASK%20where%20%7B%3Fs%20%3Fp%20%3Chttp%3A%2F%2Fo%3E.%7D'
            )
        );
        $request->headers->set('Content-Type', 'application/json');
        $request->headers->set('User-agent', 'my-sparql-client/0.1');

        /*
         * response
         */
        $expectedResponse = new Response(
            '@prefix ns0: <http://www.w3.org/2005/sparql-results#> .

[]
  a <http://www.w3.org/2005/sparql-results#results> ;
  ns0:boolean true .',
            Response::HTTP_OK,
            array(
                'Content-Type' => 'application/x-turtle'
            )
        );

        $response = $this->fixture->handleRequest($request);
        $this->assertEquals($expectedResponse, $response);
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
            '@prefix ns0: <http://www.w3.org/2005/sparql-results#> .
@prefix rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> .
@prefix xsd: <http://www.w3.org/2001/XMLSchema#> .

[]
  a ns0:ResultSet ;
  rdf:resultVariable "s"^^xsd:string, "p"^^xsd:string, "o"^^xsd:string ;
  rdf:solution [ rdf:binding [
      rdf:variable "s"^^xsd:string ;
      rdf:value <http://s>
    ], [
      rdf:variable "p"^^xsd:string ;
      rdf:value <http://p>
    ], [
      rdf:variable "o"^^xsd:string ;
      rdf:value <http://o>
    ] ] .'  ,
            Response::HTTP_OK,
            array(
                'Content-Type' => 'application/x-turtle'
            )
        );

        $response = $this->fixture->handleRequest($request);
        $this->assertEquals($expectedResponse, $response);
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
        $this->assertEquals($expectedResponse, $response);
    }
}
