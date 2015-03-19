<?php
namespace Saft\Rest;

use Saft\TestCase;
use Saft\Rest\RestApi;

class RestAPIUnitTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        
        $this->fixture = $this->getMockForAbstractClass('\Saft\Store\AbstractSparqlStore');
        
        // Override query method: it will always return the given query.
        $this->fixture->method('query')->will($this->returnArgument(0));

        $_SERVER['HTTP_ORIGIN'] = 'servername';
        $_POST['request'] = 'store/statements';
    }

    public function getTestStatement()
    {
        $statement = array('http://saft/test/s1',
            'http://saft/test/p1',
            'http://saft/test/o1',
            'http://saft/test/g1'
        );
        return $statement;
    }

    public function getTestStatements()
    {
        $statement1 = array('http://saft/test/s1',
            'http://saft/test/p1',
            'http://saft/test/o1',
            'http://saft/test/g1'
        );
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
     */
    public function testDeleteStatements()
    {
        $_POST['statementsarray'] = $this->getTestStatement();
        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        try {
            $API = new RestApi(
                $_POST['request'],
                $_SERVER['HTTP_ORIGIN'],
                $this->fixture
            );
            $query = $API->processAPI();
            $this->assertEquals(
                'DELETE DATA { Graph <http://saft/test/g1> {'.
                '<http://saft/test/s1> <http://saft/test/p1> <http://saft/test/o1>'.
                '} }',
                $query
            );
        } catch (Exception $e) {
            echo json_encode(array('error' => $e->getMessage()));
        }
    }

    /**
     * @runInSeparateProcess
     */
    public function testGetMatchingStatements()
    {
        $_POST['statementsarray'] = $this->getTestStatement();
        $_SERVER['REQUEST_METHOD'] = 'GET';
        try {
            $API = new RestApi(
                $_POST['request'],
                $_SERVER['HTTP_ORIGIN'],
                $this->fixture
            );
            $query = $API->processAPI();
            $this->assertEquals(
                'SELECT * WHERE { Graph <http://saft/test/g1> {'.
                '<http://saft/test/s1> <http://saft/test/p1> <http://saft/test/o1>'.
                '} }',
                $query
            );
        } catch (Exception $e) {
            echo json_encode(array('error' => $e->getMessage()));
        }
    }

    /**
     * @runInSeparateProcess
     */
    public function testAddStatements()
    {
        $_POST['statementsarray'] = $this->getTestStatements();
        $_SERVER['REQUEST_METHOD'] = 'POST';
        try {
            $API = new RestApi(
                $_POST['request'],
                $_SERVER['HTTP_ORIGIN'],
                $this->fixture
            );
            $query = $API->processAPI();
            $this->assertEquals(
                $query,
                'INSERT DATA { '.
                'Graph <http://saft/test/g1> {<http://saft/test/s1> <http://saft/test/p1> <http://saft/test/o1>} '.
                'Graph <http://saft/test/g2> {<http://saft/test/s2> <http://saft/test/p2> <http://saft/test/o2>} '.
                '}'
            );
        } catch (Exception $e) {
            echo json_encode(array('error' => $e->getMessage()));
        }
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
        try {
            $API = new RestApi($_POST['request'], $_SERVER['HTTP_ORIGIN'], $this->fixture);
            $query = $API->processAPI();
            $this->assertEquals(
                'INSERT DATA { Graph <> {'.
                '<http://saft/test/s1> <http://saft/test/p1> "42"^^<http://www.w3.org/2001/XMLSchema#integer>'.
                '} Graph <> {'.
                '<http://saft/test/s2> <http://saft/test/p2> ""John""^^<http://www.w3.org/2001/XMLSchema#string>'.
                '} }',
                $query
            );
        } catch (Exception $e) {
            echo json_encode(array('error' => $e->getMessage()));
        }
    }

    /**
     * @runInSeparateProcess
     */
    public function testPassGraphUri()
    {
        $_POST['statementsarray'] = array($this->getTestStatement());
        $_POST['graphUri'] = 'http://saft/test/g2';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        try {
            $API = new RestApi(
                $_POST['request'],
                $_SERVER['HTTP_ORIGIN'],
                $this->fixture
            );
            $query = $API->processAPI();
            $this->assertEquals(
                'INSERT DATA { Graph <http://saft/test/g1> {'.
                '<http://saft/test/s1> <http://saft/test/p1> <http://saft/test/o1>'.
                '} }',
                $query
            );
        } catch (Exception $e) {
            echo json_encode(array('error' => $e->getMessage()));
        }
    }

    public function testStatementPattern()
    {
        //@TODO
    }
}
