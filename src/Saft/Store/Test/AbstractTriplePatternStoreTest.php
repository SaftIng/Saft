<?php

namespace Saft\Store\Test;

use Saft\Rdf\AnyPatternImpl;
use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\LiteralImpl;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\StatementFactoryImpl;
use Saft\Rdf\StatementImpl;
use Saft\Rdf\StatementIteratorFactoryImpl;
use Saft\Sparql\SparqlUtils;
use Saft\Sparql\Query\QueryFactoryImpl;
use Saft\Store\BasicTriplePatternStore;
use Saft\Test\TestCase;

class AbstractTriplePatternStoreTest extends TestCase
{
    public function setUp()
    {
        $this->fixture = new BasicTriplePatternStore(
            new NodeFactoryImpl(),
            new StatementFactoryImpl(),
            new QueryFactoryImpl(),
            new StatementIteratorFactoryImpl()
        );
    }

    /*
     * Helper functions
     */

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

    /*
     * Tests for addStatements
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
        // create test statement instance with literal (its not in the store yet)
        $statement = $this->getTestStatementWithLiteral();

        // check that this statement does not exist already
        $resultStatements = $this->fixture->getMatchingStatements($statement);
        $this->assertEmpty($resultStatements->next());

        // create the statement
        $query = 'INSERT DATA {
            Graph <http://graph/> {
                '. $statement->getSubject()->toNQuads() .'
                '. $statement->getPredicate()->toNQuads() .'
                '. $statement->getObject()->toNQuads() .'
            }
        }';

        $this->assertNull($this->fixture->query($query));

        $resultStatements = $this->fixture->getMatchingStatements($statement);

        $this->assertEquals($statement, $resultStatements->current());
        $this->assertEmpty($resultStatements->next());
    }

    /*
     * Tests for deleteMultipleStatements
     */

    public function testDeleteMultipleStatementsExceptionCauseOfMultipleStatements()
    {
        // this test expects an exception, because more than one triple pattern is part of the data clause
        // reason for that is, that the query function rerouts to the deleteMatchingStatements function
        // which wants only a Statement.
        $this->setExpectedException('\Exception');

        $st = $this->getTestStatementWithLiteral();
        $triple = $this->getTestTriple();

        $graphPattern = SparqlUtils::statementsToSparqlFormat([$st, $triple]);
        $query = 'DELETE DATA { ' . $graphPattern . '}';

        $this->fixture->query($query);
    }

    public function testDeleteMultipleStatementsQuadRecognition()
    {
        $quad = $this->getTestQuad();
        $graphPattern = SparqlUtils::statementsToSparqlFormat(array($quad));
        $query = 'DELETE DATA { ' . $graphPattern . '}';

        $this->assertNull($this->fixture->query($query));
    }

    public function testDeleteMultipleStatementsTripleRecognition()
    {
        $triple = $this->getTestTriple();
        $graphPattern = SparqlUtils::statementsToSparqlFormat([$triple]);
        $query = 'DELETE DATA { ' . $graphPattern . '}';

        $this->assertNull($this->fixture->query($query));
    }

    public function testDeleteMultipleStatementsVariablePatterns()
    {
        $this->markTestSkipped('TODO implement test store which expects certain things on query');
        $statement = $this->getTestPatternStatement();
        $query = 'DELETE DATA { '. SparqlUtils::statementsToSparqlFormat([$statement]) .'}';

        $this->assertNull($this->fixture->query($query));
    }

    public function testDeleteMultipleStatementsStatementsWithLiteral()
    {
        $statement = $this->getTestStatementWithLiteral();

        $query = 'DELETE DATA { '. SparqlUtils::statementsToSparqlFormat([$statement]) .'}';

        $this->assertNull($this->fixture->query($query));
    }

    /*
     * Tests for hasMatchingStatement
     */

    // triple recognition
    public function testHasMatchingStatementTripleRecognition()
    {
        $triple = $this->getTestTriple();

        $this->fixture->addStatements(array($triple), $this->testGraph);

        $query = 'ASK { '. SparqlUtils::statementsToSparqlFormat(array($triple), $this->testGraph) .'}';

        $this->assertTrue($this->fixture->query($query));
    }
}
