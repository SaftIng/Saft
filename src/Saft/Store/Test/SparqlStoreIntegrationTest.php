<?php
namespace Saft\Store\Test;

use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\LiteralImpl;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\VariableImpl;
use Saft\Rdf\StatementImpl;
use Symfony\Component\Yaml\Parser;

abstract class SparqlStoreIntegrationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Saft\Cache
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
    protected $testGraphUri = 'http://localhost/Saft/TestGraph/';

    /**
     *
     */
    public function tearDown()
    {
        if (null !== $this->fixture) {
            $this->fixture->dropGraph($this->testGraphUri);
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
        // graph is empty
        $this->assertEquals(0, $this->fixture->getTripleCount($this->testGraphUri));

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
        $this->assertTrue($this->fixture->addStatements($statements, $this->testGraphUri));

        // graph has two entries
        $this->assertEquals(2, $this->fixture->getTripleCount($this->testGraphUri));
    }
    
    public function testAddStatementsLanguageTags()
    {
        // graph is empty
        $this->assertEquals(0, $this->fixture->getTripleCount($this->testGraphUri));

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
        $this->fixture->addStatements($statements, $this->testGraphUri);

        // graph has now two entries
        $this->assertEquals(2, $this->fixture->getTripleCount($this->testGraphUri));
    }
    
    public function testAddStatementsWithSuccessor()
    {
        $storeInterfaceMock = $this->getMockBuilder('Saft\Store\StoreInterface')->getMock();
        // creates a subclass of the mock and adds a dummy function
        $class = $this->className . '_testAddStatementsWithSuccessor';
        $instance = null;
        // TODO simplify that eval call or get rid of it
        // Its purpose is to create a instanciable class which implements StoreInterface. It has a certain
        // function which just return what was given. That was done to avoid working with concrete store
        // backend implementations like Virtuoso.
        eval(
            'class '. $class .' extends '. get_class($storeInterfaceMock) .' {
                public function addStatements(Saft\Rdf\StatementIterator $statements, $graphUri = null, '.
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
     * function deleteMatchingStatements
     */

    public function testDeleteMatchingStatements()
    {
        /**
         * Create some test data
         */
        $this->assertEquals(0, $this->fixture->getTripleCount($this->testGraphUri));

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
        $this->fixture->addStatements($statements, $this->testGraphUri);

        $this->assertEquals(2, $this->fixture->getTripleCount($this->testGraphUri));

        /**
         * drop all triples
         */
        $this->fixture->deleteMatchingStatements(
            new StatementImpl(new NamedNodeImpl('http://s/'), new NamedNodeImpl('http://p/'), new VariableImpl()),
            $this->testGraphUri
        );

        $this->assertEquals(0, $this->fixture->getTripleCount($this->testGraphUri));
    }

    /**
     * Tests deleteMatchingStatements
     */
    
    public function testDeleteMatchingStatementsWithSuccessor()
    {
        $storeInterfaceMock = $this->getMockBuilder('Saft\Store\StoreInterface')->getMock();
        // creates a subclass of the mock and adds a dummy function
        $class = $this->className . '_testDeleteMatchingStatementsWithSuccessor';
        $instance = null;
        // TODO simplify that eval call or get rid of it
        // Its purpose is to create a instanciable class which implements StoreInterface. It has a certain
        // function which just return what was given. That was done to avoid working with concrete store
        // backend implementations like Virtuoso.
        eval(
            'class '. $class .' extends '. get_class($storeInterfaceMock) .' {
                public function deleteMatchingStatements(Saft\Rdf\Statement $statement, $graphUri = null, '.
                    'array $options = array()) {
                    return $statement;
                }
            }
            $instance = new '. $class .'();'
        );
        
        $this->fixture->setChainSuccessor($instance);
        
        $statement = new StatementImpl(new VariableImpl(), new VariableImpl(), new VariableImpl());
        
        $this->assertTrue($this->fixture->deleteMatchingStatements($statement, $this->testGraphUri));
    }
    
    /**
     * Tests getMatchingStatements
     */
    
    public function testGetMatchingStatements()
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
        $this->fixture->addStatements($statements, $this->testGraphUri);
        
        $statement = new StatementImpl(new VariableImpl(), new VariableImpl(), new VariableImpl());
        
        $this->assertEquals(
            array(
                array(
                    $statement->getSubject()->getValue() => 'http://s/',
                    $statement->getPredicate()->getValue() => 'http://p/',
                    $statement->getObject()->getValue() => 'http://o/'
                ),
                array(
                    $statement->getSubject()->getValue() => 'http://s/',
                    $statement->getPredicate()->getValue() => 'http://p/',
                    $statement->getObject()->getValue() => 'test literal'
                ),
            ),
            $this->fixture->getMatchingStatements($statement, $this->testGraphUri)
        );
    }
    
    public function testGetMatchingStatementsEmptyGraph()
    {
        $statement = new StatementImpl(new VariableImpl(), new VariableImpl(), new VariableImpl());
        
        $this->assertEquals(
            array(),
            $this->fixture->getMatchingStatements($statement, $this->testGraphUri)
        );
    }
    
    public function testGetMatchingStatementsWithSuccessor()
    {
        $storeInterfaceMock = $this->getMockBuilder('Saft\Store\StoreInterface')->getMock();
        // creates a subclass of the mock and adds a dummy function
        $class = $this->className . '_testGetMatchingStatementsWithSuccessor';
        $instance = null;
        // TODO simplify that eval call or get rid of it
        // Its purpose is to create a instanciable class which implements StoreInterface. It has a certain
        // function which just return what was given. That was done to avoid working with concrete store
        // backend implementations like Virtuoso.
        eval(
            'class '. $class .' extends '. get_class($storeInterfaceMock) .' {
                public function getMatchingStatements(Saft\Rdf\Statement $statement, $graphUri = null, '.
                    'array $options = array()) {
                    return $statement;
                }
            }
            $instance = new '. $class .'();'
        );
        
        $this->fixture->setChainSuccessor($instance);
        
        $statement = new StatementImpl(new VariableImpl(), new VariableImpl(), new VariableImpl());
        
        $this->assertEquals(
            array(),
            $this->fixture->getMatchingStatements($statement, $this->testGraphUri)
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
        $this->fixture->addStatements($statements, $this->testGraphUri);
        
        $statement = new StatementImpl(new VariableImpl(), new VariableImpl(), new VariableImpl());
        
        $this->assertTrue($this->fixture->hasMatchingStatement($statement, $this->testGraphUri));
    }
    
    public function testHasMatchingStatementEmptyGraph()
    {
        $statement = new StatementImpl(new VariableImpl(), new VariableImpl(), new VariableImpl());
        
        // TODO known bug, that Virtuoso returns true even the graph is empty, fix that
        if ('VirtuosoIntegrationTest' === $this->className) {
            $this->assertTrue($this->fixture->hasMatchingStatement($statement, $this->testGraphUri));
            
        } else {
            $this->assertFalse($this->fixture->hasMatchingStatement($statement, $this->testGraphUri));
        }
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
        $this->fixture->addStatements($statements, $this->testGraphUri);
        
        /**
         * Mock
         */
        $storeInterfaceMock = $this->getMockBuilder('Saft\Store\StoreInterface')->getMock();
        // creates a subclass of the mock and adds a dummy function
        $class = $this->className . '_testHasMatchingStatementWithSuccessor';
        $instance = null;
        // TODO simplify that eval call or get rid of it
        // Its purpose is to create a instanciable class which implements StoreInterface. It has a certain
        // function which just return what was given. That was done to avoid working with concrete store
        // backend implementations like Virtuoso.
        eval(
            'class '. $class .' extends '. get_class($storeInterfaceMock) .' {
                public function hasMatchingStatements(Saft\Rdf\Statement $statement, $graphUri = null, '.
                    'array $options = array()) {
                    return $statement;
                }
            }
            $instance = new '. $class .'();'
        );
        
        $this->fixture->setChainSuccessor($instance);
        
        $statement = new StatementImpl(new VariableImpl(), new VariableImpl(), new VariableImpl());
        
        $this->assertTrue($this->fixture->hasMatchingStatement($statement, $this->testGraphUri));
    }

    /**
     * function query
     */

    public function testQuery()
    {
        $this->assertEquals(0, $this->fixture->getTripleCount($this->testGraphUri));

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
                new NamedNodeImpl('http://o/1')
            ),
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new NamedNodeImpl('http://o/2')
            ),
        ));

        // add triples
        $this->fixture->addStatements($statements, $this->testGraphUri);

        $this->assertEquals(
            array(array(
                's' => 'http://s/', 'p' => 'http://p/', 'o' => 'http://o/'
            ), array(
                's' => 'http://s/', 'p' => 'http://p/', 'o' => 'http://o/1'
            ), array(
                's' => 'http://s/', 'p' => 'http://p/', 'o' => 'http://o/2'
            )),
            $this->fixture->query(
                'SELECT ?s ?p ?o
                   FROM <' . $this->testGraphUri . '>
                  WHERE {?s ?p ?o.}'
            )
        );
    }

    public function testQueryAsk()
    {
        $this->assertEquals(0, $this->fixture->getTripleCount($this->testGraphUri));

        // 2 triples
        $statements = new ArrayStatementIteratorImpl(array(
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new NamedNodeImpl('http://o/')
            ),
        ));

        // add triples
        $this->fixture->addStatements($statements, $this->testGraphUri);

        $this->assertTrue(
            $this->fixture->query(
                'ASK { SELECT * FROM <'. $this->testGraphUri . '> WHERE {<http://s/> <http://p/> ?o.}}'
            )
        );
    }

    public function testQueryDifferentResultTypes()
    {
        $this->assertEquals(
            array(),
            $this->fixture->query('SELECT ?s ?p ?o FROM <'. $this->testGraphUri .'> WHERE {?s ?p ?o.}')
        );

        $this->assertEquals(
            array(),
            $this->fixture->query(
                'SELECT ?s ?p ?o FROM <'. $this->testGraphUri .'> WHERE {?s ?p ?o.}',
                array('resultType' => 'array')
            )
        );
    }

    public function testQueryEmptyResult()
    {
        $this->assertEquals(
            array(),
            $this->fixture->query('SELECT ?s ?p ?o FROM <'. $this->testGraphUri .'> WHERE {?s ?p ?o.}')
        );
    }

    public function testQueryExtendedResult()
    {
        // triples
        $statements = new ArrayStatementIteratorImpl(array(
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/1'),
                new LiteralImpl('val EN', 'en')
            ),
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/2'),
                new LiteralImpl('val DE', 'de')
            ),
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/3'),
                new LiteralImpl(1337)
            ),
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/4'),
                new LiteralImpl(0)
            ),
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/5'),
                new LiteralImpl(false)
            ),
        ));

        // add triples
        $this->fixture->addStatements($statements, $this->testGraphUri);

        $this->assertEqualsArrays(
            array(
                'head' => array(
                    'link' => array(),
                    'vars' => array('s', 'p', 'o')
                ),
                'results' => array(
                    'distinct'  => false,
                    'ordered'   => true,
                    'bindings'  => array(
                        array(
                            's' => array(
                                'type'  => 'uri',
                                'value' => 'http://s/'
                            ),
                            'p' => array(
                                'type'  => 'uri',
                                'value' => 'http://p/1'
                            ),
                            'o' => array(
                                'type'      => 'literal',
                                'value'     => 'val EN',
                                'xml:lang'  => 'en'
                            )
                        ),
                        array(
                            's' => array(
                                'type'  => 'uri',
                                'value' => 'http://s/'
                            ),
                            'p' => array(
                                'type'  => 'uri',
                                'value' => 'http://p/2'
                            ),
                            'o' => array(
                                'xml:lang'  => 'de',
                                'type'      => 'literal',
                                'value'     => 'val DE'
                            )
                        ),
                        array(
                            's' => array(
                                'type'  => 'uri',
                                'value' => 'http://s/'
                            ),
                            'p' => array(
                                'type'  => 'uri',
                                'value' => 'http://p/3'
                            ),
                            'o' => array(
                                'datatype'  => 'http://www.w3.org/2001/XMLSchema#integer',
                                'type'      => 'typed-literal',
                                'value'     => '1337'
                            )
                        ),
                        array(
                            's' => array(
                                'type'  => 'uri',
                                'value' => 'http://s/'
                            ),
                            'p' => array(
                                'type'  => 'uri',
                                'value' => 'http://p/4'
                            ),
                            'o' => array(
                                'datatype'  => 'http://www.w3.org/2001/XMLSchema#integer',
                                'type'      => 'typed-literal',
                                'value'     => '0'
                            )
                        ),
                        array(
                            's' => array(
                                'type'  => 'uri',
                                'value' => 'http://s/'
                            ),
                            'p' => array(
                                'type'  => 'uri',
                                'value' => 'http://p/5'
                            ),
                            // The value was false, but now its 0. Virtuoso cast boolean values to 0 or 1.
                            // The problem is, after you fetch entries, the datatype changed, like here, from
                            // xsd:boolean to xsd:integer.
                            // TODO how to handle boolean values? save boolean, but fetch integer later on...
                            'o' => array(
                                'datatype'  => 'http://www.w3.org/2001/XMLSchema#integer',
                                'type'      => 'typed-literal',
                                'value'     => '0'
                            )
                        )
                    )
                )
            ),
            $this->fixture->query(
                'SELECT ?s ?p ?o FROM <'. $this->testGraphUri .'> WHERE {?s ?p ?o.} ORDER BY ?p',
                array('resultType' => 'extended')
            )
        );
    }

    public function testQueryInvalidResultType()
    {
        $this->setExpectedException('\Exception');

        $this->fixture->query(
            'SELECT ?s ?p ?o FROM <'. $this->testGraphUri .'> WHERE {?s ?p ?o.}',
            array('resultType' => 'invalid')
        );
    }
    
    public function testQueryWithSuccessor()
    {
        $storeInterfaceMock = $this->getMockBuilder('Saft\Store\StoreInterface')->getMock();
        // creates a subclass of the mock and adds a dummy function
        $class = $this->className . '_testQueryWithSuccessor';
        $instance = null;
        // TODO simplify that eval call or get rid of it
        // Its purpose is to create a instanciable class which implements StoreInterface. It has a certain
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
        
        $statement = new StatementImpl(new VariableImpl(), new VariableImpl(), new VariableImpl());
        
        $this->assertEquals(
            array(),
            $this->fixture->getMatchingStatements($statement, $this->testGraphUri)
        );
    }
}
