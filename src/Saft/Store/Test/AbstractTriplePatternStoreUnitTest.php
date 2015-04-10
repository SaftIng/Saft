<?php

namespace Saft\Store\Test;

use Saft\TestCase;
use Saft\Rdf\StatementIterator;
use Saft\Rdf\LiteralImpl;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\VariableImpl;
use Saft\Rdf\Statement;
use Saft\Rdf\StatementImpl;

class AbstractTriplePatternStoreUnitTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->fixture = $this->getMockForAbstractClass('\Saft\Store\AbstractTriplePatternStore');
    }

    public function getTestQuad()
    {
        $subject1 = new NamedNodeImpl('http://saft/test/s1');
        $predicate1 = new NamedNodeImpl('http://saft/test/p1');
        $object1 = new NamedNodeImpl('http://saft/test/o1');
        $graph1 = new NamedNodeImpl('http://saft/test/g1');
        $quad = new StatementImpl($subject1, $predicate1, $object1, $graph1);

        return $quad;
    }

    public function getTestTriple()
    {
        $subject2 = new NamedNodeImpl('http://saft/test/s2');
        $predicate2 = new NamedNodeImpl('http://saft/test/p2');
        $object2 = new NamedNodeImpl('http://saft/test/o2');
        $triple = new StatementImpl($subject2, $predicate2, $object2);

        return $triple;
    }

    public function getTestPatternStatement()
    {
        $subject1 = new VariableImpl('?s1');
        $predicate1 = new VariableImpl('?p1');
        $object1 = new VariableImpl('?o1');
        $triple = new StatementImpl($subject1, $predicate1, $object1);

        return $triple;
    }

    public function getTestStatementWithLiteral()
    {
        $subject2 = new NamedNodeImpl('http://saft/test/s1');
        $predicate2 = new NamedNodeImpl('http://saft/test/p2');
        $object2 = new LiteralImpl("John");
        $triple = new StatementImpl($subject2, $predicate2, $object2);

        return $triple;
    }

    public function testAddStatements()
    {
        $triple = $this->getTestTriple();
        $quad = $this->getTestQuad();
        $query = 'INSERT DATA { '. $triple->toSparqlFormat(). $quad->toSparqlFormat(). '}';
        
        // Override abstract-methods: it will check the statements
        $this->fixture
            ->expects($this->once())
            ->method('addStatements')
            ->will(
                $this->returnCallback(
                    function (StatementIterator $statements, $graphUri = null, array $options = array()) {
                        \PHPUnit_Framework_Assert::assertEquals(
                            $statements->current()->toSparqlFormat(),
                            $this->getTestTriple()->toSparqlFormat()
                        );
                        $statements->next();
                        \PHPUnit_Framework_Assert::assertEquals(
                            $statements->current()->toSparqlFormat(),
                            $this->getTestQuad()->toSparqlFormat()
                        );
                    }
                )
            );
        $this->fixture->query($query);
    }

    public function testTripleRecognition()
    {
        $query = 'DELETE DATA { '.$this->getTestTriple()->toSparqlFormat().'}';
        
        $this->fixture
            ->expects($this->once())
            ->method('deleteMatchingStatements')
            ->will(
                $this->returnCallback(
                    function (Statement $statement, $graphUri = null, array $options = array()) {
                        \PHPUnit_Framework_Assert::assertEquals(
                            $statement->toSparqlFormat(),
                            $this->getTestTriple()->toSparqlFormat()
                        );
                    }
                )
            );
        $this->fixture->query($query);
    }

    public function testQuadRecognition()
    {
        $query = 'DELETE DATA { '.$this->getTestQuad()->toSparqlFormat().'}';
        
        $this->fixture
            ->expects($this->once())
            ->method('deleteMatchingStatements')
            ->will(
                $this->returnCallback(
                    function (Statement $statement, $graphUri = null, array $options = array()) {
                        \PHPUnit_Framework_Assert::assertEquals(
                            $statement->toSparqlFormat(),
                            $this->getTestQuad()->toSparqlFormat()
                        );
                    }
                )
            );
        $this->fixture->query($query);
    }

    public function testVariablePatterns()
    {
        $query = 'DELETE DATA { '.$this->getTestPatternStatement()->toSparqlFormat().'}';
        
        $this->fixture
            ->expects($this->once())
            ->method('deleteMatchingStatements')
            ->will(
                $this->returnCallback(
                    function (Statement $statement, $graphUri = null, array $options = array()) {
                        \PHPUnit_Framework_Assert::assertEquals(
                            $statement->toSparqlFormat(),
                            $this->getTestPatternStatement()->toSparqlFormat()
                        );
                    }
                )
            );
        $this->fixture->query($query);
    }

    public function testStatementsWithLiteral()
    {
        $query = 'DELETE DATA { '.$this->getTestStatementWithLiteral()->toSparqlFormat().'}';
        
        $this->fixture
            ->expects($this->once())
            ->method('deleteMatchingStatements')
            ->will(
                $this->returnCallback(
                    function (Statement $statement, $graphUri = null, array $options = array()) {
                        \PHPUnit_Framework_Assert::assertEquals(
                            $statement->toSparqlFormat(),
                            $this->getTestStatementWithLiteral()->toSparqlFormat()
                        );
                    }
                )
            );
        $this->fixture->query($query);
    }
}
