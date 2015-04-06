<?php

namespace Saft\Store\Test;

use Saft\TestCase;
use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\LiteralImpl;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\Statement;
use Saft\Rdf\StatementImpl;

class AbstractSparqlStoreUnitTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        
        $this->fixture = $this->getMockForAbstractClass('\Saft\Store\AbstractSparqlStore');
        
        // Override query method: it will always return the given query.
        $this->fixture->method('query')->will($this->returnArgument(0));
    }

    public function getTestStatement()
    {
        $subject1 = new NamedNodeImpl('http://saft/test/s1');
        $predicate1 = new NamedNodeImpl('http://saft/test/p1');
        $object1 = new NamedNodeImpl('http://saft/test/o1');
        $graph1 = new NamedNodeImpl('http://saft/test/g1');
        $triple1 = new StatementImpl($subject1, $predicate1, $object1, $graph1);

        return $triple1;
    }

    /**
     *
     */
    public function getFilledTestArrayStatementIterator()
    {
        $subject2 = new NamedNodeImpl('http://saft/test/s2');
        $predicate2 = new NamedNodeImpl('http://saft/test/p2');
        $object2 = new NamedNodeImpl('http://saft/test/o2');
        $graph2 = new NamedNodeImpl('http://saft/test/g2');
        $quad1 = new StatementImpl($subject2, $predicate2, $object2, $graph2);

        $statements = new ArrayStatementIteratorImpl(array($this->getTestStatement(), $quad1));

        return $statements;
    }

    /**
     *
     */
    public function testGetMatchingStatements()
    {
        $query = $this->fixture->getMatchingStatements($this->getTestStatement());
        $this->assertEquals(
            'SELECT * WHERE { Graph <http://saft/test/g1> {'.
            '<http://saft/test/s1> <http://saft/test/p1> <http://saft/test/o1>'.
            '} }',
            $query
        );
    }

    /**
     * s
     */
    public function testAddStatements()
    {
        $query = $this->fixture->addStatements($this->getFilledTestArrayStatementIterator());

        $this->assertEquals(
            'INSERT DATA { '.
            'Graph <http://saft/test/g1> {<http://saft/test/s1> <http://saft/test/p1> <http://saft/test/o1>} '.
            'Graph <http://saft/test/g2> {<http://saft/test/s2> <http://saft/test/p2> <http://saft/test/o2>} '.
            '}',
            $query
        );
    }

    /**
     *
     */
    public function testDeleteMatchingStatements()
    {
        $query = $this->fixture->deleteMatchingStatements($this->getTestStatement());
        //echo $query;
        $this->assertEquals(
            'DELETE DATA { Graph <http://saft/test/g1> '.
            '{<http://saft/test/s1> <http://saft/test/p1> <http://saft/test/o1>} }',
            $query
        );
    }

    /**
     *
     */
    public function testhasMatchingStatement()
    {
        $query = $this->fixture->hasMatchingStatement($this->getTestStatement());
        
        $this->assertEquals(
            'ASK { Graph <http://saft/test/g1> {'.
            '<http://saft/test/s1> <http://saft/test/p1> <http://saft/test/o1>} }',
            $query
        );
    }

    public function testMultipleVariatonOfStatements()
    {
        /**
         * object is a number
         */
        $subject1 = new NamedNodeImpl('http://saft/test/s1');
        $predicate1 = new NamedNodeImpl('http://saft/test/p1');
        $object1 = new LiteralImpl(42);
        $triple1 = new StatementImpl($subject1, $predicate1, $object1);

        /**
         * object is a literal
         */
        $object2 = new LiteralImpl('"John"');
        $triple2 = new StatementImpl($subject1, $predicate1, $object2);
        
        // Setup array statement iterator
        $statements = new ArrayStatementIteratorImpl(array($triple1, $triple2));

        // add test statements
        $query = $this->fixture->addStatements($statements, $this->testGraphUri);
        $this->assertEquals(
            'INSERT DATA { '.
            'Graph <'. $this->testGraphUri .'> {'.
            '<http://saft/test/s1> <http://saft/test/p1> "42"^^<http://www.w3.org/2001/XMLSchema#integer> .} '.
            'Graph <'. $this->testGraphUri .'> {'.
            '<http://saft/test/s1> <http://saft/test/p1> "John"^^<http://www.w3.org/2001/XMLSchema#string> .} '.
            '}',
            $query
        );

        /**
         * use the given graphUri
         */
        $statements = new ArrayStatementIteratorImpl(array($triple1));
        
        $query = $this->fixture->addStatements($statements, 'http://saft/test/graph');
        
        $this->assertEquals(
            $query,
            'INSERT DATA { Graph <http://saft/test/graph> {'.
            '<http://saft/test/s1> <http://saft/test/p1> "42"^^<http://www.w3.org/2001/XMLSchema#integer> .'.
            '} }'
        );
    }
}
