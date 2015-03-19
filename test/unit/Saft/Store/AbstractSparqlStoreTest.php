<?php

namespace Saft\Store;

use Saft\Rdf\Statement;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\LiteralImpl;
use Saft\Rdf\Variable;
use Saft\Rdf\StatementImpl;
use Saft\Rdf\ArrayStatementIteratorImpl;

class AbstractSparqlStoreTest extends TestCase
{
    /**
     * Mock for AbstractSparqlStore
     */
    protected $fixture;

    public function setUp()
    {
        parent::setUp();
        
        $this->fixture = $this->getMockForAbstractClass('\Saft\Store\AbstractSparqlStore');
        
        // Override query method: it will always return the given query.
        $this->fixture->method('query')->will($this->returnArgument(0));
    }

    public function testCreateStatement()
    {
        $subject1 = new NamedNodeImpl('http://saft/test/s1');
        $predicate1 = new NamedNodeImpl('http://saft/test/p1');
        $object1 = new NamedNodeImpl('http://saft/test/o1');
        $graph1 = new NamedNodeImpl(null);
        $triple1 = new StatementImpl($subject1, $predicate1, $object1, $graph1);

        return $triple1;
    }

    /**
     * @depends testCreateStatement
     */
    public function testCreateStatements(Statement $statement1)
    {
        $subject2 = new NamedNodeImpl('http://saft/test/s2');
        $predicate2 = new NamedNodeImpl('http://saft/test/p2');
        $object2 = new NamedNodeImpl('http://saft/test/o2');
        $graph2 = new NamedNodeImpl('http://saft/test/g2');
        $quad1 = new StatementImpl($subject2, $predicate2, $object2, $graph2);

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
        $query = $this->fixture->getMatchingStatements($statement);
        $this->assertEquals(
            $query,
            'SELECT * WHERE {<http://saft/test/s1> <http://saft/test/p1> <http://saft/test/o1>.}'
        );
    }

    /**
     * @depends testCreateStatements
     */
    public function testAddStatements(ArrayStatementIteratorImpl $statements)
    {
        $query = $this->fixture->addStatements($statements);

        $this->assertEquals(
            $query,
            'INSERT DATA {<http://saft/test/s1> <http://saft/test/p1> <http://saft/test/o1>.'.
            'Graph <http://saft/test/g2> {<http://saft/test/s2> <http://saft/test/p2> <http://saft/test/o2>.}}'
        );
    }

    /**
     * @depends testCreateStatement
     */
    public function testDeleteMatchingStatements(Statement $statement)
    {
        $query = $this->fixture->deleteMatchingStatements($statement);
        //echo $query;
        $this->assertEquals(
            $query,
            'DELETE DATA {<http://saft/test/s1> <http://saft/test/p1> <http://saft/test/o1>.}'
        );
    }

    /**
     * @depends testCreateStatement
     */
    public function testhasMatchingStatement(Statement $statement)
    {
        $query = $this->fixture->hasMatchingStatement($statement);
        //echo $query;
        $this->assertEquals(
            $query,
            'ASK {<http://saft/test/s1> <http://saft/test/p1> <http://saft/test/o1>.}'
        );
    }

    public function testLiteralImplInStatements()
    {
        /**
         * object is a number
         */
        $subject1 = new NamedNodeImpl('http://saft/test/s1');
        $predicate1 = new NamedNodeImpl('http://saft/test/p1');
        $object1 = new LiteralImpl(42);
        $graph1 = new NamedNodeImpl(null);
        $triple1 = new StatementImpl($subject1, $predicate1, $object1, $graph1);

        /**
         * object is a Literal
         */
        $object2 = new LiteralImpl('"John"');
        $triple2 = new StatementImpl($subject1, $predicate1, $object2, $graph1);
        
        // Setup array statement iterator
        $statements = new \Saft\Rdf\ArrayStatementIteratorImpl(array($triple1, $triple2));

        // add test statements
        $query = $this->fixture->addStatements($statements);
        $this->assertEquals(
            $query,
            'INSERT DATA {'.
            '<http://saft/test/s1> <http://saft/test/p1> "42"^^<http://www.w3.org/2001/XMLSchema#integer>.'.
            '<http://saft/test/s1> <http://saft/test/p1> ""John""^^<http://www.w3.org/2001/XMLSchema#string>.'.
            '}'
        );
    }

    /**
     * @depends testCreateStatement
     */
    public function testGivenGraphUri(Statement $statement)
    {
         /**
         * use the given graphUri
         */
        $statements = new \Saft\Rdf\ArrayStatementIteratorImpl(array($statement));
        
        $query = $this->fixture->addStatements($statements, 'http://saft/test/graph');
        
        $this->assertEquals(
            $query,
            'INSERT DATA {Graph <http://saft/test/graph> {'.
            '<http://saft/test/s1> <http://saft/test/p1> <http://saft/test/o1>.'.
            '}}'
        );

        /**
         * use graphPattern
         */
        $query = $this->fixture->addStatements($statements, '?graph');
        $this->assertEquals(
            $query,
            'INSERT DATA {Graph ?graph {'.
            '<http://saft/test/s1> <http://saft/test/p1> <http://saft/test/o1>.'.
            '}}'
        );

        /**
         * use bad graphUri
         */
         $this->setExpectedException('\Exception');
          $query = $this->fixture->addStatements($statements, 'foo');
    }

    public function testpatternInStatements()
    {
        $subject1 = new NamedNodeImpl('http://saft/test/s1');
        $predicate1 = new NamedNodeImpl('http://saft/test/p1');
        $object1 = new Variable('?otest');
        $graph1 = new NamedNodeImpl(null);
        $triple1 = new StatementImpl($subject1, $predicate1, $object1, $graph1);

        $query = $this->fixture->hasMatchingStatement($triple1);
        $this->assertEquals(
            $query,
            'ASK {<http://saft/test/s1> <http://saft/test/p1> ?otest.}'
        );
    }
}
