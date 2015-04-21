<?php

namespace Saft\Store\Test;

use Saft\TestCase;
use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\LiteralImpl;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\Statement;
use Saft\Rdf\StatementIterator;
use Saft\Rdf\StatementImpl;
use Saft\Rdf\VariableImpl;

class AbstractTriplePatternStoreUnitTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->fixture = $this->getMockForAbstractClass('\Saft\Store\AbstractTriplePatternStore');
    }

    protected function getTestQuad()
    {
        $subject1 = new NamedNodeImpl('http://saft/testquad/s1');
        $predicate1 = new NamedNodeImpl('http://saft/testquad/p1');
        $object1 = new NamedNodeImpl('http://saft/testquad/o1');
        $graph1 = new NamedNodeImpl('http://saft/testquad/g1');
        $quad = new StatementImpl($subject1, $predicate1, $object1, $graph1);

        return new StatementImpl($subject1, $predicate1, $object1, $graph1);
    }

    protected function getTestTriple()
    {
        $subject2 = new NamedNodeImpl('http://saft/testtriple/s2');
        $predicate2 = new NamedNodeImpl('http://saft/testtriple/p2');
        $object2 = new NamedNodeImpl('http://saft/testtriple/o2');
        $triple = new StatementImpl($subject2, $predicate2, $object2);

        return new StatementImpl($subject2, $predicate2, $object2);
    }

    protected function getTestPatternStatement()
    {
        $subject1 = new VariableImpl('?s1');
        $predicate1 = new VariableImpl('?p1');
        $object1 = new VariableImpl('?o1');

        return new StatementImpl($subject1, $predicate1, $object1);
    }

    protected function getTestStatementWithLiteral()
    {
        $subject2 = new NamedNodeImpl('http://saft/test/s1');
        $predicate2 = new NamedNodeImpl('http://saft/test/p2');
        $object2 = new LiteralImpl('John');
        return new StatementImpl($subject2, $predicate2, $object2);
    }

    /**
     * Tests addStatements
     */

    public function testAddStatementsNoTriplesAndQuads()
    {
        // it throws an error because query contains NO triples or quads.
        $this->setExpectedException('\Exception');

        $query = 'INSERT DATA {  }';
        $this->fixture->query($query);
    }

    public function testAddStatementsTriples()
    {
        $statement = $this->getTestStatementWithLiteral();
        $statementIterator = new ArrayStatementIteratorImpl(array($statement));
        $query = 'INSERT DATA { 
            Graph <http://graph/> { 
                '. $statement->getSubject()->toNQuads() .'
                '. $statement->getPredicate()->toNQuads() .'
                '. $statement->getObject()->toNQuads() .'
            }
        }';

        $this->assertEquals(
            array(
                $statementIterator,
                null,
                array()
            ),
            $this->fixture->query($query)
        );
    }

    /**
     * Tests deleteMultipleStatements
     */

    public function testDeleteMultipleStatements()
    {
        $st = $this->getTestStatementWithLiteral();
        $triple = $this->getTestTriple();
        $query = 'DELETE DATA { '. $st->toSparqlFormat(). $triple->toSparqlFormat(). '}';
        $this->setExpectedException('\Exception');
        $this->fixture->query($query);
    }

    public function testDeleteMultipleStatementsQuadRecognition()
    {
        $quad = $this->getTestQuad();
        $query = 'DELETE DATA { '. $quad->toSparqlFormat() .'}';

        $this->assertEquals(
            array(
                $quad,
                null,   // $graphUri
                array() // $options
            ),
            $this->fixture->query($query)
        );
    }

    public function testDeleteMultipleStatementsVariablePatterns()
    {
        $statement = $this->getTestPatternStatement();
        $query = 'DELETE DATA { '. $statement->toSparqlFormat() .'}';

        $this->assertEquals(
            array(
                $statement,
                null,   // $graphUri
                array() // $options
            ),
            $this->fixture->query($query)
        );
    }

    public function testDeleteMultipleStatementsStatementsWithLiteral()
    {
        $statement = $this->getTestStatementWithLiteral();

        $query = 'DELETE DATA { '. $statement->toSparqlFormat() .'}';

        $this->assertEquals(
            array(
                $statement,
                null,   // $graphUri
                array() // $options
            ),
            $this->fixture->query($query)
        );
    }

    /**
     * Tests hasMatchingStatement > triple recognition
     */

    public function testHasMatchingStatementTripleRecognition()
    {
        $triple = $this->getTestTriple();
        $query = 'ASK { '. $triple->toSparqlFormat() .'}';

        $this->assertEquals(
            array(
                $triple,
                null,   // $graphUri
                array() // $options
            ),
            $this->fixture->query($query)
        );
    }
}
