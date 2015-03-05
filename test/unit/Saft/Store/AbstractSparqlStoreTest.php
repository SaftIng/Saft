<?php

namespace Saft\Store;

use Saft\Rdf\Statement;
use Saft\Rdf\ArrayStatementIteratorImpl;

class AbstractSparqlStoreTest extends TestCase
{

    public function setUp()
    {
        parent::setUp();
        $this->store = $this->getMockForAbstractClass(
            '\Saft\Store\AbstractSparqlStore'
        );
        //override query methode
        $this->store->method('query')
             ->will($this->returnArgument(0));
    }

    public function testCreateStatement()
    {
        $subject1 = new \Saft\Rdf\NamedNode('http://saft/test/s1');
        $predicate1 = new \Saft\Rdf\NamedNode('http://saft/test/p1');
        $object1 = new \Saft\Rdf\NamedNode('http://saft/test/o1');
        $graph1 = new \Saft\Rdf\NamedNode(null);
        $triple1 = new \Saft\Rdf\StatementImpl($subject1, $predicate1, $object1, $graph1);

        return $triple1;
    }

    /**
     * @depends testCreateStatement
     */
    public function testCreateStatements(Statement $statement1)
    {
        $subject2 = new \Saft\Rdf\NamedNode('http://saft/test/s2');
        $predicate2 = new \Saft\Rdf\NamedNode('http://saft/test/p2');
        $object2 = new \Saft\Rdf\NamedNode('http://saft/test/o2');
        $graph2 = new \Saft\Rdf\NamedNode('http://saft/test/g2');
        $quad1 = new \Saft\Rdf\StatementImpl($subject2, $predicate2, $object2, $graph2);

        $statements = new \Saft\Rdf\ArrayStatementIteratorImpl(
            array($statement1, $quad1)
        );

        return $statements;
    }

    /**
     * @depends testCreateStatement
     */
    public function testGetMatchingStatements(Statement $statement)
    {
        $query = $this->store->getMatchingStatements($statement);
        $this->assertEquals(
            $query,
            'SELECT * WHERE {Graph <> {<http://saft/test/s1> <http://saft/test/p1> <http://saft/test/o1>.} }'
        );
    }

    /**
     * @depends testCreateStatements
     */
    public function testAddStatements(ArrayStatementIteratorImpl $statements)
    {
        $query = $this->store->addStatements($statements);

        $this->assertEquals(
            $query,
            "INSERT DATA {Graph <> {<http://saft/test/s1> <http://saft/test/p1> <http://saft/test/o1>.}"
            . " Graph <http://saft/test/g2> {<http://saft/test/s2> <http://saft/test/p2> <http://saft/test/o2>.} }"
        );
    }

    /**
     * @depends testCreateStatement
     */
    public function testDeleteMatchingStatements(Statement $statement)
    {
        $query = $this->store->deleteMatchingStatements($statement);
        //echo $query;
        $this->assertEquals(
            $query,
            'DELETE DATA {Graph <> {<http://saft/test/s1> <http://saft/test/p1> <http://saft/test/o1>.} }'
        );
    }

    /**
     * @depends testCreateStatement
     */
    public function testhasMatchingStatement(Statement $statement)
    {
        $query = $this->store->hasMatchingStatement($statement);
        //echo $query;
        $this->assertEquals(
            $query,
            'ASK {Graph <> {<http://saft/test/s1> <http://saft/test/p1> <http://saft/test/o1>.} }'
        );
    }

    public function testMultipleVariatonOfStatements()
    {
        //object is a number
        $subject1 = new \Saft\Rdf\NamedNode('http://saft/test/s1');
        $predicate1 = new \Saft\Rdf\NamedNode('http://saft/test/p1');
        $object1 = new \Saft\Rdf\Literal(42);
        $graph1 = new \Saft\Rdf\NamedNode(null);
        $triple1 = new \Saft\Rdf\StatementImpl($subject1, $predicate1, $object1, $graph1);

        //object is a literal
        $object2 = new \Saft\Rdf\Literal('"John"');
        $triple2 = new \Saft\Rdf\StatementImpl($subject1, $predicate1, $object2, $graph1);
        $statements = new \Saft\Rdf\ArrayStatementIteratorImpl(
            array($triple1, $triple2)
        );

        $query = $this->store->addStatements($statements);
        $this->assertEquals(
            $query,
            'INSERT DATA {Graph <> {<http://saft/test/s1> <http://saft/test/p1> "42"^^<http://www.w3.org/2001/XMLSchema#integer>.}'
            .' Graph <> {<http://saft/test/s1> <http://saft/test/p1> ""John""^^<http://www.w3.org/2001/XMLSchema#string>.} }'
        );

        
        //use the given graphUri
        $statements = new \Saft\Rdf\ArrayStatementIteratorImpl(
            array($triple1)
        );
        $query = $this->store->addStatements($statements, "http://saft/test/graph");
        $this->assertEquals(
            $query,
            'INSERT DATA {Graph <http://saft/test/graph> {<http://saft/test/s1> <http://saft/test/p1> "42"^^<http://www.w3.org/2001/XMLSchema#integer>.} }'
        );
    }

    public function tearDown()
    {
        unset($this->store);
        parent::tearDown();
    }
}
