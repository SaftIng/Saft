<?php
namespace Saft\Store\Test;

use Saft\TestCase;
use Saft\Test\MockStore;
use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\LiteralImpl;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\StatementImpl;
use Saft\Rdf\AnyPatternImpl;
use Saft\Sparql\SparqlUtils;

class AbstractTriplePatternStoreTest extends TestCase
{
    public function setUp()
    {
        $this->fixture = new BasicTriplePatternStore();
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
        $subject1 = new AnyPatternImpl();
        $predicate1 = new AnyPatternImpl();
        $object1 = new AnyPatternImpl();

        return new StatementImpl($subject1, $predicate1, $object1);
    }

    protected function getTestStatementWithLiteral()
    {
        $subject2 = new NamedNodeImpl('http://saft/test/s1');
        $predicate2 = new NamedNodeImpl('http://saft/test/p2');
        $object2 = new LiteralImpl('John');
        return new StatementImpl($subject2, $predicate2, $object2);
    }


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
        $query = 'INSERT DATA {
            Graph <http://graph/> {
                '. $statement->getSubject()->toNQuads() .'
                '. $statement->getPredicate()->toNQuads() .'
                '. $statement->getObject()->toNQuads() .'
            }
        }';

        $resultStatements = $this->fixture->getMatchingStatements($statement);
        $this->assertEmpty($resultStatements->next());

        $this->assertTrue($this->fixture->query($query));

        $resultStatements = $this->fixture->getMatchingStatements($statement);
        $this->assertEquals($resultStatements->next(), $statement);
        $this->assertEmpty($resultStatements->next());
    }

    /**
     * Tests deleteMultipleStatements
     */

    public function testDeleteMultipleStatements()
    {
        $st = $this->getTestStatementWithLiteral();
        $triple = $this->getTestTriple();

        $graphPattern = SparqlUtils::statementsToSparqlFormat([$st, $triple]);
        $query = 'DELETE DATA { ' . $graphPattern . '}';
        
        $this->fixture->query($query);
    }

    public function testDeleteMultipleStatementsQuadRecognition()
    {
        $quad = $this->getTestQuad();
        $graphPattern = SparqlUtils::statementsToSparqlFormat([$quad]);
        $query = 'DELETE DATA { ' . $graphPattern . '}';

        $this->assertTrue($this->fixture->query($query));
    }

    public function testDeleteMultipleStatementsVariablePatterns()
    {
        $this->markTestSkipped("TODO implement test store which expects certain things on query");
        $statement = $this->getTestPatternStatement();
        $query = 'DELETE DATA { '. SparqlUtils::statementsToSparqlFormat([$statement]) .'}';

        $this->assertTrue($this->fixture->query($query));
    }

    public function testDeleteMultipleStatementsStatementsWithLiteral()
    {
        $statement = $this->getTestStatementWithLiteral();

        $query = 'DELETE DATA { '. SparqlUtils::statementsToSparqlFormat([$statement]) .'}';

        $this->assertTrue($this->fixture->query($query));
    }

    /**
     * Tests hasMatchingStatement > triple recognition
     */

    public function testHasMatchingStatementTripleRecognition()
    {
        $triple = $this->getTestTriple();
        $query = 'ASK { '. SparqlUtils::statementsToSparqlFormat([$triple]) .'}';

        $this->assertTrue($this->fixture->query($query));
    }
}
