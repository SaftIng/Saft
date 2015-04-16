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
        $subject1 = new NamedNodeImpl('http://saft/testquad/s1');
        $predicate1 = new NamedNodeImpl('http://saft/testquad/p1');
        $object1 = new NamedNodeImpl('http://saft/testquad/o1');
        $graph1 = new NamedNodeImpl('http://saft/testquad/g1');
        $quad = new StatementImpl($subject1, $predicate1, $object1, $graph1);

        return $quad;
    }

    public function getTestTriple()
    {
        $subject2 = new NamedNodeImpl('http://saft/testtriple/s2');
        $predicate2 = new NamedNodeImpl('http://saft/testtriple/p2');
        $object2 = new NamedNodeImpl('http://saft/testtriple/o2');
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
        $this->markTestSkipped("I can't understand this test please add more documentation");
        $st = $this->getTestStatementWithLiteral();
        $triple = $this->getTestTriple();
        $quad = $this->getTestQuad();
        $query = 'INSERT DATA { '. $st->toSparqlFormat(). $triple->toSparqlFormat()
            . $quad->toSparqlFormat(). '}';
        
        // Override abstract-methods: it will check the statements
        $this->fixture
            ->expects($this->once())
            ->method('addStatements')
            ->will(
                $this->returnCallback(
                    function (StatementIterator $fStatements, $fGraphUri = null, array $fOptions = array()) use ($triple, $quad, $st) {
                        TestCase::assertEquals(
                            $fStatements->current()->toSparqlFormat(),
                            $st->toSparqlFormat()
                        );
                        $fStatements->next();
                        TestCase::assertEquals(
                            $fStatements->current()->toSparqlFormat(),
                            $triple->toSparqlFormat()
                        );
                        $fStatements->next();
                        TestCase::assertEquals(
                            $fStatements->current()->toSparqlFormat(),
                            $quad->toSparqlFormat()
                        );
                    }
                )
            );
        $this->fixture->query($query);
    }

    public function testDeleteMultipleStatements()
    {
        $st = $this->getTestStatementWithLiteral();
        $triple = $this->getTestTriple();
        $query = 'DELETE DATA { '. $st->toSparqlFormat(). $triple->toSparqlFormat(). '}';
        $this->setExpectedException('\Exception');
        $this->fixture->query($query);
    }

    public function testTripleRecognition()
    {
        $triple = $this->getTestTriple();
        $query = 'ASK { '.$triple->toSparqlFormat().'}';
        $this->overrideMethodWithAssertion('hasMatchingStatement', $triple);
        $this->fixture->query($query);
    }

    //@TODO does not recognize quads.
    public function testQuadRecognition()
    {
        $quad = $this->getTestQuad();
        $query = 'DELETE DATA { '.$quad->toSparqlFormat().'}';
        $this->overrideMethodWithAssertion('deleteMatchingStatements', $quad);
        $this->fixture->query($query);
    }

    //@TODO does not recognize quads.
    public function testVariablePatterns()
    {
        $statement = $this->getTestPatternStatement();
        $query = 'DELETE DATA { '.$statement->toSparqlFormat().'}';
        $this->overrideMethodWithAssertion('deleteMatchingStatements', $statement);
        $this->fixture->query($query);
    }

    public function testStatementsWithLiteral()
    {
        $statement = $this->getTestStatementWithLiteral();
        $query = 'DELETE DATA { '.$statement->toSparqlFormat().'}';
        $this->overrideMethodWithAssertion('deleteMatchingStatements', $statement);
        $this->fixture->query($query);
    }

    /**
     * Overrides method given by $method with an assertion about equivalence
     *  - $statement and the Statement of the called method.
     *  - @TODO $graphUri and GraphUri of the called method.
     *  - @TODO $options and the Options of the called method.
     * @param  string    $method    method in AbstractTriplePatternStore to override
     * @param  Statement $statement
     * @param  string    $graphUri
     * @param  array     $options
     */
    private function overrideMethodWithAssertion($method, Statement $statement, $graphUri = null, array $options = array())
    {
        $this->markTestSkipped("I can't understand this test please add more documentation");
        $this->fixture
            ->expects($this->once())
            ->method($method)
            ->will(
                $this->returnCallback(
                    function (Statement $fStatement, $fGraphUri = null, array $fOptions = array()) use ($statement, $graphUri, $options) {
                        TestCase::assertEquals(
                            $fStatement->toSparqlFormat(),
                            $statement->toSparqlFormat()
                        );
                    }
                )
            );
    }
}
