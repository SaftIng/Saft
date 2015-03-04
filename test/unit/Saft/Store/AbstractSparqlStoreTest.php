<?php

namespace Saft\Store;

//TODO wait until Rdf\Statement is ready.
class AbstractSparqlStoreTest extends \PHPUnit_Framework_TestCase
{

    protected function setUp()
    {
        $this->store = $this->getMockForAbstractClass(
            '\Saft\Store\AbstractSparqlStore'
        );
    }

    public function testCreateStatements()
    {
        /**$statement1 = new \Saft\Rdf\Triple('a1', 'b1', 'c1');
        $statement2 = new \Saft\Rdf\Quad('a2', 'b2', 'c2', 'd2');

        $statements = array($statement1, $statement2);

        return $statements;*/
    }

    public function testCreateStatement()
    {
        /*$statement = new \Saft\Rdf\TripleNEW('a1', 'b1', 'c1');

        return $statement;*/
    }

    /**
     * @depends testCreateStatement
     */
    /*public function testGetMatchingStatements($statement)
    {
        $query = $this->store->getMatchingStatements($statement);
        $this->assertEquals(
            $query, "Select * \n"
            ."WHERE\n"
            . "{\n"
            . "<a1> <b1> <c1>.\n"
            ."}"
        );
    }*/

    /**
     * @depends testCreateStatements
     */
    /*public function testAddStatements(array $statements)
    {
        $query = $this->store->addStatements($statements);
        //echo $query;
        $this->assertEquals(
            $query, "Insert DATA\n"
            . "{\n"
            . "<a1> <b1> <c1>.\n"
            ."Graph <d2> {<a2> <b2> <c2>.}\n"
            ."}"
        );
    }*/

    /**
     * @depends testCreateStatement
     */
    /*public function testDeleteMatchingStatements($statement)
    {
        $query = $this->store->deleteMatchingStatements($statement);
        //echo $query;
        $this->assertEquals(
            $query, "Delete DATA\n"
            . "{\n"
            . "<a1> <b1> <c1>.\n"
            ."}"
        );
    }*/

    /**
     * @depends testCreateStatement
     */
    /*public function testhasMatchingStatement($statement)
    {
        $query = $this->store->hasMatchingStatement($statement);
        //echo $query;
        $this->assertEquals(
            $query, "ASK\n"
            . "{\n"
            . "<a1> <b1> <c1>.\n"
            ."}"
        );
    }*/

    /*public function testMultipleVariatonOfStatements()
    {
        //object is a number
        $statement1 = new \Saft\Rdf\TripleNEW('a1', 'b1', 42);
        //object is a literal
        $statement2 = new \Saft\Rdf\TripleNEW('a2', 'b2', '"John"');
        $statements = array($statement1, $statement2);

        $query = $this->store->addStatements($statements);
        $this->assertEquals(
            $query, "Insert DATA\n"
            . "{\n"
            . "<a1> <b1> 42.\n"
            . "<a2> <b2> \"John\".\n"
            ."}"
        );

        //use the given graphUri
        $statement3 = new \Saft\Rdf\TripleNEW('a3', 'b3', 'c3');
        $statements = array($statement3);
        $query = $this->store->addStatements($statements, 'graph');
        $this->assertEquals(
            $query, "Insert DATA\n"
            . "{\n"
            ."Graph <graph> {<a3> <b3> <c3>.}\n"
            ."}"
        );
    }*/

    public function tearDown()
    {
        unset($this->store);
    }
}
