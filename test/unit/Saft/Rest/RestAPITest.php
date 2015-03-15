<?php
namespace Saft\Rest;

use Saft\Rest\RestApi;

class RestAPITest extends \Saft\Store\TestCase
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

        $_SERVER['HTTP_ORIGIN'] = 'servername';
        $_POST['request'] = 'store/statements';
    }

    public function testCreateStatement()
    {
        $statement = array('http://saft/test/s1',
            'http://saft/test/p1',
            'http://saft/test/o1',
            'http://saft/test/g1'
        );
        return $statement;
    }

    /**
     * @depends testCreateStatement
     */
    public function testCreateStatements(array $statement1)
    {
        $statement2 = array('http://saft/test/s2',
            'http://saft/test/p2',
            'http://saft/test/o2',
            'http://saft/test/g2'
        );
        $statements = array($statement1, $statement2);
        return $statements;
    }

    /**
     * @runInSeparateProcess
     * @depends testCreateStatement
     */
    public function testDeleteStatements(array $statement)
    {
        $_POST['statementsarray'] = $statement;
        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $this->calRestApi('DELETE DATA {Graph <http://saft/test/g1> {'.
                '<http://saft/test/s1> <http://saft/test/p1> <http://saft/test/o1>.'.
                '} }');
    }

    /**
     * @runInSeparateProcess
     * @depends testCreateStatement
     */
    public function testgetMatchingStatements(array $statement)
    {
        $_POST['statementsarray'] = $statement;
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->calRestApi('SELECT * WHERE {Graph <http://saft/test/g1> {'.
                '<http://saft/test/s1> <http://saft/test/p1> <http://saft/test/o1>.'.
                '} }');
    }

    /**
     * @runInSeparateProcess
     * @depends testCreateStatements
     */
    public function testAddStatements(array $statements)
    {
        $_POST['statementsarray'] = $statements;
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->calRestApi('INSERT DATA {Graph <http://saft/test/g1> {'.
                '<http://saft/test/s1> <http://saft/test/p1> <http://saft/test/o1>.'.
                '}'.
                ' Graph <http://saft/test/g2> {<http://saft/test/s2> <http://saft/test/p2> <http://saft/test/o2>.} }');
    }

    /**
     * @runInSeparateProcess
     */
    public function testObjectAsLiteral()
    {
        //object is a number
        $statement1 = array('http://saft/test/s1',
            'http://saft/test/p1',
            42,
        );
        //object is a literal
        $statement2 = array('http://saft/test/s2',
            'http://saft/test/p2',
            '"John"',
        );
        $statements = array($statement1, $statement2);
        $_POST['statementsarray'] = $statements;
        

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->calRestApi('INSERT DATA {Graph <> {'.
                '<http://saft/test/s1> <http://saft/test/p1> "42"^^<http://www.w3.org/2001/XMLSchema#integer>.'.
                '} Graph <> {'.
                '<http://saft/test/s2> <http://saft/test/p2> ""John""^^<http://www.w3.org/2001/XMLSchema#string>.'.
                '} }');
    }

    /**
     * @runInSeparateProcess
     * @depends testCreateStatement
     */
    public function testPassGraphUri(array $statement)
    {
        /**
         * use given graphUri
         */
        $_POST['statementsarray'] = array($statement);
        $_POST['graphUri'] = 'http://saft/test/g2';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->calRestApi('INSERT DATA {Graph <http://saft/test/g2> {'.
                '<http://saft/test/s1> <http://saft/test/p1> <http://saft/test/o1>.'.
                '} }');

        /**
         * use graphPattern
         */
        $_POST['graphUri'] = '?graph';
        $this->calRestApi('INSERT DATA {Graph ?graph {'.
                '<http://saft/test/s1> <http://saft/test/p1> <http://saft/test/o1>.'.
                '} }');

        /**
         * use bad graphUri
         */
        $_POST['graphUri'] = 'foo';
        try {
            $API = new RestApi(
                $_POST['request'],
                $_SERVER['HTTP_ORIGIN'],
                $this->fixture
            );
            $this->setExpectedException('\Exception');
            $query = $API->processAPI();
        } catch (Exception $e) {
            echo json_encode(array('error' => $e->getMessage()));
        }
    }

    /**
     * @runInSeparateProcess
     */
    public function testStatementPattern()
    {
        $statement = array('http://saft/test/s1',
            'http://saft/test/p1',
            '?ob',
        );

        $_POST['statementsarray'] = $statement;
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->calRestApi('SELECT * WHERE {Graph <> {'.
                '<http://saft/test/s1> <http://saft/test/p1> ?ob.'.
                '} }');
    }

    private function calRestApi($value)
    {
        try {
            $API = new RestApi(
                $_POST['request'],
                $_SERVER['HTTP_ORIGIN'],
                $this->fixture
            );
            $query = $API->processAPI();
            $this->assertEquals(
                $query,
                $value
            );
        } catch (Exception $e) {
            echo json_encode(array('error' => $e->getMessage()));
        }
    }
}
