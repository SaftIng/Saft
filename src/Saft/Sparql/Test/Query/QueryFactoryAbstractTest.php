<?php

namespace Saft\Sparql\Test\Query;

use Saft\Sparql\Query\AskQueryImpl;
use Saft\Sparql\Query\DescribeQueryImpl;
use Saft\Sparql\Query\GraphQueryImpl;
use Saft\Sparql\Query\QueryFactoryImpl;
use Saft\Sparql\Query\SelectQueryImpl;
use Saft\Sparql\Query\UpdateQueryImpl;
use Saft\Test\TestCase;

abstract class QueryFactoryAbstractTest extends TestCase
{
    /**
     * Returns subject to test.
     *
     * @return QueryFactory
     */
    abstract public function newInstance();

    public function setUp()
    {
        parent::setUp();

        $this->fixture = $this->newInstance();
    }

    /*
     * Tests for createInstanceByQueryString
     */

    public function testCreateInstanceByQueryStringAskQuery()
    {
        $query = 'PREFIX foaf: <http://xmlns.com/foaf/0.1/>
                  ASK  { ?x foaf:name  "Alice" ;
                  foaf:mbox  <mailto:alice@work.example> }';

        $this->assertEquals(
            new AskQueryImpl($query),
            $this->fixture->createInstanceByQueryString($query)
        );
    }

    public function testCreateInstanceByQueryStringDescribeQuery()
    {
        $query = 'PREFIX foaf: <http://xmlns.com/foaf/0.1/>
                  DESCRIBE ?x
                  WHERE { ?x foaf:mbox <mailto:alice@org> }';

        $this->assertEquals(
            new DescribeQueryImpl($query),
            $this->fixture->createInstanceByQueryString($query)
        );
    }

    public function testCreateInstanceByQueryStringGraphQuery()
    {
        $query = 'CREATE GRAPH <'. $this->testGraph->getUri() .'>';

        $this->assertEquals(
            new GraphQueryImpl($query),
            $this->fixture->createInstanceByQueryString($query)
        );
    }

    public function testCreateInstanceByQueryStringSelectQuery()
    {
        $query = ' SELECT ?x
                  FROM <'. $this->testGraph->getUri() .'>
                  WHERE { ?x foaf:mbox <mailto:alice@org> }';

        $this->assertEquals(
            new SelectQueryImpl($query),
            $this->fixture->createInstanceByQueryString($query)
        );
    }

    public function testCreateInstanceByQueryStringUnknownType()
    {
        $this->setExpectedException('\Exception');

        $query = ' unknown type';
        $this->fixture->createInstanceByQueryString($query);
    }

    public function testCreateInstanceByQueryStringUpdateQuery()
    {
        /**
         * INSERT DATA
         */
        $query = 'PREFIX foaf: <http://xmlns.com/foaf/0.1/>
                  INSERT DATA {Graph <>}';

        $this->assertEquals(
            new UpdateQueryImpl($query),
            $this->fixture->createInstanceByQueryString($query)
        );

        /**
         * INSERT INTO GRAPH
         */
        $query = 'INSERT INTO GRAPH <> { ... }';

        $this->assertEquals(
            new UpdateQueryImpl($query),
            $this->fixture->createInstanceByQueryString($query)
        );

        /**
         * WITH ... DELETE ... WHERE
         */
        $query = 'WITH <'. $this->testGraph->getUri() .'> DELETE { ... } WHERE { ... }';

        $this->assertEquals(
            new UpdateQueryImpl($query),
            $this->fixture->createInstanceByQueryString($query)
        );

        /**
         * DELETE DATA
         */
        $query = 'PREFIX foaf: <http://xmlns.com/foaf/0.1/>
                  DELETE DATA {}';

        $this->assertEquals(
            new UpdateQueryImpl($query),
            $this->fixture->createInstanceByQueryString($query)
        );
    }
}
