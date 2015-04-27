<?php
namespace Saft\Store\Test;

use Saft\TestCase;
use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\LiteralImpl;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\StatementImpl;
use Saft\Rdf\VariableImpl;
use Saft\Store\Result\StatementResult;
use Saft\Store\Result\SetResult;
use Saft\Store\Result\ValueResult;
use Symfony\Component\Yaml\Parser;

abstract class AbstractSparqlStoreIntegrationTest extends TestCase
{
    /**
     * @var Cache
     */
    protected $cache;

    /**
     * @var array
     */
    protected $config;

    /**
     * Contains an instance of the class to test.
     *
     * @var mixed
     */
    protected $fixture;

    /**
     * @var string
     */
    protected $testGraph;

    public function setUp()
    {
        $this->testGraph = new NamedNodeImpl('http://localhost/Saft/TestGraph/');

        parent::setUp();
    }

    /**
     *
     */
    public function tearDown()
    {
        // TODO there is no dropGraph method on stores
        if (null !== $this->fixture) {
            $this->fixture->dropGraph($this->testGraph);
        }

        parent::tearDown();
    }

    /**
     * http://stackoverflow.com/a/12496979
     * Fixes assertEquals in case of check array equality.
     *
     * @param array  $expected
     * @param array  $actual
     * @param string $message  optional
     */
    protected function assertEqualsArrays($expected, $actual, $message = '')
    {
        sort($expected);
        sort($actual);

        $this->assertEquals($expected, $actual, $message);
    }

    /**
     * Loads config.yml and return its content as array.
     */
    public function getConfigContent()
    {
        // set path to test dir
        $saftRootDir = dirname(__FILE__) . '/../../../../';
        $configFilepath = $saftRootDir . 'test-config.yml';

        // check for config file
        if (false === file_exists($configFilepath)) {
            throw new \Exception('test-config.yml missing');
        }

        // parse YAML file
        $yaml = new Parser();
        return $yaml->parse(file_get_contents($configFilepath));
    }

    /**
     * function addStatements
     */

    public function testAddStatements()
    {
        // remove all triples from the test graph
        $this->fixture->query('CLEAR GRAPH <' . $this->testGraph->getUri() . '>');

        // graph is empty
        $this->assertEquals(0, $this->fixture->getTripleCount($this->testGraph));

        // 2 triples
        $statements = new ArrayStatementIteratorImpl(array(
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new NamedNodeImpl('http://o/')
            ),
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new LiteralImpl('test literal')
            ),
        ));

        // add triples
        $this->assertTrue($this->fixture->addStatements($statements, $this->testGraph));

        // graph has two entries
        $this->assertEquals(2, $this->fixture->getTripleCount($this->testGraph));
    }

    public function testAddStatementsInvalidStatements()
    {
        // build statement iterator containing one statement which consists only of variables.
        $statements = new ArrayStatementIteratorImpl(array(
            new StatementImpl(new VariableImpl(), new VariableImpl(), new VariableImpl())
        ));

        // expect exception, because only concrete (no variable) statements are allowed
        $this->setExpectedException('\Exception');
        $this->fixture->addStatements($statements);
    }

    public function testAddStatementsLanguageTags()
    {
        // graph is empty
        $this->assertEquals(0, $this->fixture->getTripleCount($this->testGraph));

        // 2 triples
        $statements = new ArrayStatementIteratorImpl(array(
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new LiteralImpl('test literal', 'en')
            ),
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new LiteralImpl('test literal', 'de')
            ),
        ));

        // add triples
        $this->fixture->addStatements($statements, $this->testGraph);

        // graph has now two entries
        $this->assertEquals(2, $this->fixture->getTripleCount($this->testGraph));
    }
    
    public function testAddStatementsUseStatementGraph()
    {
        // remove all triples from the test graph
        $this->fixture->query('CLEAR GRAPH <' . $this->testGraph->getUri() . '>');

        // graph is empty
        $this->assertEquals(0, $this->fixture->getTripleCount($this->testGraph));

        // 2 triples
        $statements = new ArrayStatementIteratorImpl(array(
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new NamedNodeImpl('http://o/'),
                $this->testGraph
            ),
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new LiteralImpl('test literal'),
                $this->testGraph
            ),
        ));

        // add triples
        $this->assertTrue($this->fixture->addStatements($statements));

        // graph has two entries
        $this->assertEquals(2, $this->fixture->getTripleCount($this->testGraph));
    }

    public function testAddStatementsWithSuccessor()
    {
        $storeInterfaceMock = $this->getMockBuilder('Saft\Store\Store')->getMock();
        // creates a subclass of the mock and adds a dummy function
        $class = $this->className . '_testAddStatementsWithSuccessor';
        $instance = null;
        // TODO simplify that eval call or get rid of it
        // Its purpose is to create a instanciable class which implements Store. It has a certain
        // function which just return what was given. That was done to avoid working with concrete store
        // backend implementations like Virtuoso.
        eval(
            'class '. $class .' extends '. get_class($storeInterfaceMock) .' {
                public function addStatements(Saft\Rdf\StatementIterator $statements, Saft\Rdf\Node $graph = null, '.
                    'array $options = array()) {
                    return $statements;
                }
            }
            $instance = new '. $class .'();'
        );

        $this->fixture->setChainSuccessor($instance);

        $this->assertTrue($this->fixture->addStatements(new ArrayStatementIteratorImpl(array())));
    }

    /**
     * Tests deleteMatchingStatements
     */

    public function testDeleteMatchingStatements()
    {
        /**
         * Create some test data
         */
        $this->assertEquals(0, $this->fixture->getTripleCount($this->testGraph));

        // 2 triples
        $statements = new ArrayStatementIteratorImpl(array(
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new NamedNodeImpl('http://o/')
            ),
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new LiteralImpl('test literal')
            ),
        ));

        // add triples
        $this->fixture->addStatements($statements, $this->testGraph);

        $this->assertEquals(2, $this->fixture->getTripleCount($this->testGraph));

        /**
         * drop all triples
         */
        $this->fixture->deleteMatchingStatements(
            new StatementImpl(new NamedNodeImpl('http://s/'), new NamedNodeImpl('http://p/'), new VariableImpl()),
            $this->testGraph
        );

        $this->assertEquals(0, $this->fixture->getTripleCount($this->testGraph));
    }

    public function testDeleteMatchingStatementsNoGraphGiven()
    {
        // expect exception thrown, because no graph was given, neither set in Statement nor given extra
        $this->setExpectedException('\Exception');
        
        $this->fixture->deleteMatchingStatements(
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new VariableImpl()
            )
        );
    }

    public function testDeleteMatchingStatementsUseStatementGraph()
    {
        /**
         * Create some test data
         */
        $this->assertEquals(0, $this->fixture->getTripleCount($this->testGraph));

        // 2 triples
        $statements = new ArrayStatementIteratorImpl(array(
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new NamedNodeImpl('http://o/'),
                $this->testGraph
            ),
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new LiteralImpl('test literal'),
                $this->testGraph
            ),
        ));

        // add triples
        $this->fixture->addStatements($statements, $this->testGraph);

        $this->assertEquals(2, $this->fixture->getTripleCount($this->testGraph));

        /**
         * drop all triples
         */
        $this->fixture->deleteMatchingStatements(
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new VariableImpl(),
                $this->testGraph
            )
        );

        $this->assertEquals(0, $this->fixture->getTripleCount($this->testGraph));
    }

    /**
     * Tests deleteMatchingStatements
     */

    public function testDeleteMatchingStatementsWithSuccessor()
    {
        $storeInterfaceMock = $this->getMockBuilder('Saft\Store\Store')->getMock();
        // creates a subclass of the mock and adds a dummy function
        $class = $this->className . '_testDeleteMatchingStatementsWithSuccessor';
        $instance = null;
        // TODO simplify that eval call or get rid of it
        // Its purpose is to create a instanciable class which implements Store. It has a certain
        // function which just return what was given. That was done to avoid working with concrete store
        // backend implementations like Virtuoso.
        eval(
            'class '. $class .' extends '. get_class($storeInterfaceMock) .' {
                public function deleteMatchingStatements(Saft\Rdf\Statement $statement, Saft\Rdf\Node $graph = null, '.
                    'array $options = array()) {
                    return $statement;
                }
            }
            $instance = new '. $class .'();'
        );

        $this->fixture->setChainSuccessor($instance);

        $statement = new StatementImpl(new VariableImpl(), new VariableImpl(), new VariableImpl());

        $this->assertTrue($this->fixture->deleteMatchingStatements($statement, $this->testGraph));
    }

    public function testDeleteMatchingStatementsWithVariables()
    {
        /**
         * Create some test data
         */
        $this->assertEquals(0, $this->fixture->getTripleCount($this->testGraph));

        // 2 triples
        $statements = new ArrayStatementIteratorImpl(array(
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new NamedNodeImpl('http://o/')
            ),
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new LiteralImpl('test literal')
            ),
        ));

        // add triples
        $this->fixture->addStatements($statements, $this->testGraph);

        $this->assertEquals(2, $this->fixture->getTripleCount($this->testGraph));

        /**
         * drop all triples
         */
        $this->fixture->deleteMatchingStatements(
            new StatementImpl(new VariableImpl(), new VariableImpl(), new VariableImpl()),
            $this->testGraph
        );

        $this->assertEquals(0, $this->fixture->getTripleCount($this->testGraph));
    }

    /**
     * Tests getMatchingStatements
     */

    public function testGetMatchingStatements1()
    {
        // 2 triples
        $statements = new ArrayStatementIteratorImpl(array(
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new NamedNodeImpl('http://o/')
            ),
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new LiteralImpl('test literal')
            ),
        ));

        // add triples
        $this->fixture->addStatements($statements, $this->testGraph);

        $statement = new StatementImpl(
            new NamedNodeImpl('http://s/'),
            new NamedNodeImpl('http://p/'),
            new VariableImpl()
        );

        /**
         * Build SetResult instance to check against
         */
        $statementResultToCheckAgainst = new StatementResult();
        $statementResultToCheckAgainst->setVariables(array('s', 'p', 'o'));
        $statementResultToCheckAgainst->append(
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new NamedNodeImpl('http://o/')
            )
        );
        $statementResultToCheckAgainst->append(
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new LiteralImpl('test literal')
            )
        );

        $this->assertEquals(
            $statementResultToCheckAgainst,
            $this->fixture->getMatchingStatements($statement, $this->testGraph)
        );
    }

    public function testGetMatchingStatementsEmptyGraph()
    {
        $statement = new StatementImpl(new VariableImpl(), new VariableImpl(), new VariableImpl());
        $statementResult = new StatementResult();
        $statementResult->setVariables(array('s', 'p', 'o'));

        $this->assertEquals(
            $statementResult,
            $this->fixture->getMatchingStatements($statement, $this->testGraph)
        );
    }

    public function testGetMatchingStatementsWithSuccessor()
    {
        $storeInterfaceMock = $this->getMockBuilder('Saft\Store\Store')->getMock();
        // creates a subclass of the mock and adds a dummy function
        $class = $this->className . '_testGetMatchingStatementsWithSuccessor';
        $instance = null;
        // TODO simplify that eval call or get rid of it
        // Its purpose is to create a instanciable class which implements Store. It has a certain
        // function which just return what was given. That was done to avoid working with concrete store
        // backend implementations like Virtuoso.
        eval(
            'class '. $class .' extends '. get_class($storeInterfaceMock) .' {
                public function getMatchingStatements(Saft\Rdf\Statement $statement, Saft\Rdf\Node $graph = null, '.
                    'array $options = array()) {
                    return $statement;
                }
            }
            $instance = new '. $class .'();'
        );

        $this->fixture->setChainSuccessor($instance);

        $statement = new StatementImpl(new VariableImpl(), new VariableImpl(), new VariableImpl());

        $statementResult = new StatementResult();
        $statementResult->setVariables(array('s', 'p', 'o'));

        $this->assertEquals(
            $statementResult,
            $this->fixture->getMatchingStatements($statement, $this->testGraph)
        );
    }

    /**
     * Tests hasMatchingStatements
     */

    public function testHasMatchingStatement()
    {
        // 2 triples
        $statements = new ArrayStatementIteratorImpl(array(
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new NamedNodeImpl('http://o/')
            ),
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new LiteralImpl('test literal')
            ),
        ));

        // add triples
        $this->fixture->addStatements($statements, $this->testGraph);

        $statement = new StatementImpl(new VariableImpl(), new VariableImpl(), new VariableImpl());

        $this->assertTrue($this->fixture->hasMatchingStatement($statement, $this->testGraph));
    }

    public function testHasMatchingStatementEmptyGraph()
    {
        $this->fixture->query('CLEAR GRAPH <'. $this->testGraph->getUri() .'>');

        $statement = new StatementImpl(new VariableImpl(), new VariableImpl(), new VariableImpl());

        $this->assertFalse($this->fixture->hasMatchingStatement($statement, $this->testGraph));
    }

    public function testHasMatchingStatementWithSuccessor()
    {
        // 2 triples
        $statements = new ArrayStatementIteratorImpl(array(
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new NamedNodeImpl('http://o/')
            ),
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new LiteralImpl('test literal')
            ),
        ));

        // add triples
        $this->fixture->addStatements($statements, $this->testGraph);

        /**
         * Mock
         */
        $storeInterfaceMock = $this->getMockBuilder('Saft\Store\Store')->getMock();
        // creates a subclass of the mock and adds a dummy function
        $class = $this->className . '_testHasMatchingStatementWithSuccessor';
        $instance = null;
        // TODO simplify that eval call or get rid of it
        // Its purpose is to create a instanciable class which implements Store. It has a certain
        // function which just return what was given. That was done to avoid working with concrete store
        // backend implementations like Virtuoso.
        eval(
            'class '. $class .' extends '. get_class($storeInterfaceMock) .' {
                public function hasMatchingStatements(Saft\Rdf\Statement $statement, Saft\Rdf\Node $graph = null, '.
                    'array $options = array()) {
                    return $statement;
                }
            }
            $instance = new '. $class .'();'
        );

        $this->fixture->setChainSuccessor($instance);

        $statement = new StatementImpl(new VariableImpl(), new VariableImpl(), new VariableImpl());

        $this->assertTrue($this->fixture->hasMatchingStatement($statement, $this->testGraph));
    }

    /**
     * Tests query
     */

    public function testQuery()
    {
        $this->fixture->query('CLEAR GRAPH <'. $this->testGraph->getUri() .'>');

        // 2 triples
        $statements = new ArrayStatementIteratorImpl(array(
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new NamedNodeImpl('http://o/')
            ),
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new LiteralImpl('foobar')
            ),
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new LiteralImpl(42)
            ),
        ));

        // add triples
        $this->fixture->addStatements($statements, $this->testGraph);

        /**
         * Build SetResult instance to check against
         */
        $setResultToCheckAgainst = new SetResult();
        $setResultToCheckAgainst->setVariables(array('s', 'o'));
        $setResultToCheckAgainst->append(array(
            's' => new NamedNodeImpl('http://s/'),
            'o' => new LiteralImpl('42')
        ));
        $setResultToCheckAgainst->append(array(
            's' => new NamedNodeImpl('http://s/'),
            'o' => new LiteralImpl('foobar')
        ));
        $setResultToCheckAgainst->append(array(
            's' => new NamedNodeImpl('http://s/'),
            'o' => new NamedNodeImpl('http://o/')
        ));

        // check
        $this->assertEquals(
            $setResultToCheckAgainst,
            $this->fixture->query(
                'SELECT ?s ?o FROM <' . $this->testGraph->getUri() . '> WHERE {?s ?p ?o.} ORDER BY ?o'
            )
        );
    }

    public function testQueryAsk()
    {
        $this->fixture->query('CLEAR GRAPH <'. $this->testGraph->getUri() .'>');

        $this->assertEquals(0, $this->fixture->getTripleCount($this->testGraph));

        // 2 triples
        $statements = new ArrayStatementIteratorImpl(array(
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new NamedNodeImpl('http://o/')
            ),
        ));

        // add triples
        $this->fixture->addStatements($statements, $this->testGraph);

        $this->assertEquals(
            new ValueResult(true),
            $this->fixture->query(
                'ASK { SELECT * FROM <'. $this->testGraph->getUri() . '> WHERE {<http://s/> <http://p/> ?o.}}'
            )
        );
    }

    public function testQueryEmptyResult()
    {
        $setResult = new SetResult();
        $setResult->setVariables(array('s', 'p', 'o'));

        $this->assertEquals(
            $setResult,
            $this->fixture->query('SELECT ?s ?p ?o FROM <'. $this->testGraph->getUri() .'> WHERE {?s ?p ?o.}')
        );
    }

    public function testQueryWithSuccessor()
    {
        $storeInterfaceMock = $this->getMockBuilder('Saft\Store\Store')->getMock();
        // creates a subclass of the mock and adds a dummy function
        $class = $this->className . '_testQueryWithSuccessor';
        $instance = null;
        // TODO simplify that eval call or get rid of it
        // Its purpose is to create a instanciable class which implements Store. It has a certain
        // function which just return what was given. That was done to avoid working with concrete store
        // backend implementations like Virtuoso.
        eval(
            'class '. $class .' extends '. get_class($storeInterfaceMock) .' {
                public function query($query, array $options = array()) {
                    return $query;
                }
            }
            $instance = new '. $class .'();'
        );

        $this->fixture->setChainSuccessor($instance);

        $setResult = new SetResult();
        $setResult->setVariables(array('s', 'p', 'o'));

        $this->assertEquals(
            $setResult,
            $this->fixture->query('SELECT ?s ?p ?o FROM <'. $this->testGraph->getUri() .'> WHERE {?s ?p ?o.}')
        );
    }
}
