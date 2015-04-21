<?php

namespace Saft\Store\Test;

use Saft\TestCase;
use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\LiteralImpl;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\VariableImpl;
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

        //test to add not concrete Statement
        $subject1 = new VariableImpl('?s1');
        $predicate1 = new NamedNodeImpl('http://saft/test/p1');
        $object1 = new NamedNodeImpl('http://saft/test/o1');
        $graph1 = new NamedNodeImpl('http://saft/test/g1');
        $triple1 = new StatementImpl($subject1, $predicate1, $object1, $graph1);
        $statements = new ArrayStatementIteratorImpl(array($triple1));
        $this->setExpectedException('\Exception');
        $this->fixture->addStatements($statements);
    }

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

    public function testhasMatchingStatement()
    {
        $query = $this->fixture->hasMatchingStatement($this->getTestStatement());
        
        $this->assertEquals(
            'ASK { Graph <http://saft/test/g1> {'.
            '<http://saft/test/s1> <http://saft/test/p1> <http://saft/test/o1>} }',
            $query
        );
    }

    public function testMultipleVariatonOfObjects()
    {
        /**
         * object is a number
         */
        $subject1 = new NamedNodeImpl('http://saft/test/s1');
        $predicate1 = new NamedNodeImpl('http://saft/test/p1');
        $object1 = new LiteralImpl(42); // will be handled as string, because no datatype given.
        $triple1 = new StatementImpl($subject1, $predicate1, $object1);

        /**
         * object is a literal
         */
        $object2 = new LiteralImpl('John');
        $triple2 = new StatementImpl($subject1, $predicate1, $object2);
        
        // Setup array statement iterator
        $statements = new ArrayStatementIteratorImpl(array($triple1, $triple2));

        // add test statements
        $query = $this->fixture->addStatements($statements, $this->testGraphUri);
        $this->assertEquals(
            'INSERT DATA { '.
            'Graph <'. $this->testGraphUri .'> {'.
            '<http://saft/test/s1> <http://saft/test/p1> "42"^^<http://www.w3.org/2001/XMLSchema#string>} '.
            'Graph <'. $this->testGraphUri .'> {'.
            '<http://saft/test/s1> <http://saft/test/p1> "John"^^<http://www.w3.org/2001/XMLSchema#string>} '.
            '}',
            $query
        );
    }

    /**
     * test if pattern-variable is recognized properly.
     */
    public function testPatternStatement()
    {
        /**
         * subject is a pattern variable
         */
        $subject = new VariableImpl('?s1');
        $predicate = new NamedNodeImpl('http://saft/test/p1');
        $object = new NamedNodeImpl('http://saft/test/o1');
        $triple = new StatementImpl($subject, $predicate, $object);

        $query = $this->fixture->hasMatchingStatement($triple);
        
        $this->assertEquals(
            'ASK { ?s1 <http://saft/test/p1> <http://saft/test/o1> . }',
            $query
        );

        /**
         * graph is a pattern variable
         */
        $graph1 = new VariableImpl('?g1');
        $statement = new StatementImpl($subject, $predicate, $object, $graph1);

        $query = $this->fixture->hasMatchingStatement($statement);
        
        $this->assertEquals(
            'ASK { Graph ?g1 {?s1 <http://saft/test/p1> <http://saft/test/o1>} }',
            $query
        );
    }

    /**
     * test if given graphUri is preferred.
     */
    public function testAddStatementsWithGraphUri()
    {
        // Setup array statement iterator
        $statements = new ArrayStatementIteratorImpl(array($this->getTestStatement()));
        
        // use the given graphUri
        $query = $this->fixture->addStatements($statements, 'http://saft/test/foograph');
        
        $this->assertEquals(
            $query,
            'INSERT DATA { Graph <http://saft/test/foograph> {'.
            '<http://saft/test/s1> <http://saft/test/p1> <http://saft/test/o1>'.
            '} }'
        );

        // use the given graphUri-variable
        $query = $this->fixture->addStatements($statements, '?foo');
        
        $this->assertEquals(
            $query,
            'INSERT DATA { Graph ?foo {'.
            '<http://saft/test/s1> <http://saft/test/p1> <http://saft/test/o1>'.
            '} }'
        );
    }
}
