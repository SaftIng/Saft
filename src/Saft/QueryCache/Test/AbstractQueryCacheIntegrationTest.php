<?php

namespace Saft\QueryCache\Test;

use Saft\TestCase;
use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\LiteralImpl;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\StatementImpl;
use Saft\Rdf\VariableImpl;
use Saft\Sparql\Query\AbstractQuery;
use Symfony\Component\Yaml\Parser;

/**
 * That abstract class provides tests for the QueryCache component. But it will not be executed directly but
 * over subclasses with cache backend as suffix, such as QueryCacheFileCacheTest.php.
 *
 * This way we can run all the tests for different configuration with minimum overhead.
 */
abstract class AbstractQueryCacheIntegrationTest extends TestCase
{
    /**
     * @var string
     */
    protected $className = '';
    
    /**
     * Used in pattern key's as seperator. Here an example for _:
     * http://localhost/Saft/TestGraph/_http://a_*_*
     * 
     * @var string
     */
    protected $separator = '__.__';
    
    /**
     *
     */
    public function setUp()
    {
        parent::setUp();
        
        // set path to test dir
        $saftRootDir = dirname(__FILE__) . '/../../../../';
        $configFilepath = $saftRootDir . 'test-config.yml';
        // check for config file, if it does not exist, skip entire test.
        if (false === file_exists($configFilepath)) {
            $this->markTestSkipped('File test-config.yml not found, skip test for QueryCache.');
        }
        // parse YAML file
        $yaml = new Parser();
        $this->config = $yaml->parse(file_get_contents($configFilepath));
    }
    
    /**
     * Tests addStatements
     */
     
    public function testAddStatements()
    {
        // set basic store as successor
        $successor = new BasicStore();
        $this->fixture->setChainSuccessor($successor);
        
        // build testdata
        $statement = new StatementImpl(new VariableImpl(), new VariableImpl(), new VariableImpl());
        $statementIterator = new ArrayStatementIteratorImpl(array($statement));
        
        // assumption is that all given parameter will be returned
        $this->assertEquals(
            array($statementIterator, $this->testGraphUri, array(1)),
            $this->fixture->addStatements($statementIterator, $this->testGraphUri, array(1))
        );
    }
     
    // try to call function method without a successor set leads to an exception
    public function testAddStatementsNoSuccessor()
    {
        $this->setExpectedException('\Exception');
        
        $statement = new StatementImpl(new VariableImpl(), new VariableImpl(), new VariableImpl());
        $statementIterator = new ArrayStatementIteratorImpl(array($statement));
        
        $this->fixture->addStatements($statementIterator);
    }
    
    /**
     * Tests buildPatternListBySPO
     */
     
    public function testBuildPatternListBySPO3Placeholders()
    {
        $this->assertEquals(
            array(
                $this->testGraphUri . $this->separator .'*'. $this->separator .'*'. $this->separator .'*'
            ),
            $this->fixture->buildPatternListBySPO(
                '*', // s
                '*', // p
                '*', // o
                $this->testGraphUri
            )
        );
    }
     
    public function testBuildPatternListBySPOWithUriAndPlaceholders()
    {
        $sUri = 'http://foo/subject';
        
        $this->assertEquals(
            array(
                $this->testGraphUri . $this->separator .'*'. $this->separator .'*'. $this->separator .'*',
                $this->testGraphUri . $this->separator . $sUri . $this->separator .'*'. $this->separator .'*'
            ),
            $this->fixture->buildPatternListBySPO(
                $sUri, // s
                '*', // p
                '*', // o
                $this->testGraphUri
            )
        );
    }
    
    /**
     * Tests buildPatternListByStatement
     */
     
    public function testBuildPatternListByStatement()
    {
        $statement = new StatementImpl(
            new NamedNodeImpl('http://a'), 
            new NamedNodeImpl('http://b'), 
            new NamedNodeImpl('http://c')
        );
        
        $this->assertEquals(
            array(
                
                // graphUri_*_*_*
                'http://localhost/Saft/TestGraph/'. $this->separator .'*'. $this->separator .'*'. $this->separator .'*',
                
                /**
                 * 1 place set: graphUri_http://a_*_*
                 */
                'http://localhost/Saft/TestGraph/'. $this->separator .'http://a'. $this->separator .
                    '*'. $this->separator .'*',
                'http://localhost/Saft/TestGraph/'. $this->separator .'*'. $this->separator .'http://b'. 
                    $this->separator .'*',
                'http://localhost/Saft/TestGraph/'. $this->separator .'*'. $this->separator .'*'. 
                    $this->separator .'http://c',
                
                /**
                 * 2 places set: graphUri_http://a_http://b_*
                 */
                'http://localhost/Saft/TestGraph/'. $this->separator .'http://a'. $this->separator .'http://b'. 
                    $this->separator .'*',
                'http://localhost/Saft/TestGraph/'. $this->separator .'http://a'. $this->separator .'*'. 
                    $this->separator .'http://c',
                'http://localhost/Saft/TestGraph/'. $this->separator .'*'. $this->separator .'http://b'. 
                    $this->separator .'http://c',
                    
                /**
                 * all 3 places set
                 */
                'http://localhost/Saft/TestGraph/'. $this->separator .'http://a'. $this->separator .'http://b'. 
                    $this->separator .'http://c',
            ),
            $this->fixture->buildPatternListByStatement($statement, $this->testGraphUri)
        );
    }
    
    public function testBuildPatternListByStatementOnlyVariables()
    {
        $statement = new StatementImpl(new VariableImpl(), new VariableImpl(), new VariableImpl());
        
        $this->assertEquals(
            array(
                $this->testGraphUri . $this->separator .'*'. $this->separator .'*'. $this->separator .'*',
            ),
            $this->fixture->buildPatternListByStatement($statement, $this->testGraphUri)
        );
    }
    
    /**
     * Tests buildPatternListByTriplePattern
     */
    
    public function testBuildPatternListByTriplePattern()
    {
        $statement = new StatementImpl(new VariableImpl(), new VariableImpl(), new VariableImpl());
        
        $this->assertEquals(
            array(
                $this->testGraphUri . $this->separator .'*'. $this->separator .'*'. $this->separator .'*',
            
                /**
                 * 1 place set
                 */
                $this->testGraphUri . $this->separator .'http://a'. $this->separator .'*'. $this->separator .'*',
                $this->testGraphUri . $this->separator .'*'. $this->separator .'http://b'. $this->separator .'*',
                $this->testGraphUri . $this->separator .'*'. $this->separator .'*'. $this->separator .'http://c',
                
                /**
                 * 2 places set
                 */
                $this->testGraphUri . $this->separator .'http://a'. $this->separator .'http://b'. 
                    $this->separator .'*',
                $this->testGraphUri . $this->separator .'http://a'. $this->separator .'*'. 
                    $this->separator .'http://c',
                $this->testGraphUri . $this->separator .'*'. $this->separator .'http://b'. 
                    $this->separator .'http://c',
                    
                // all 3 places set
                $this->testGraphUri . $this->separator .'http://a'. $this->separator .'http://b'. 
                    $this->separator .'http://c',
            ),
            $this->fixture->buildPatternListByTriplePattern(
                array(
                    's' => 'http://a',
                    'p' => 'http://b',
                    'o' => 'http://c',
                    's_type' => 'uri',
                    'p_type' => 'uri',
                    'o_type' => 'uri',
                ), 
                $this->testGraphUri
            )
        );
    }
    
    /**
     * Tests deleteMatchingStatements
     */
     
    public function testDeleteMatchingStatements()
    {
        // set basic store as successor
        $successor = new BasicStore();
        $this->fixture->setChainSuccessor($successor);
        
        // build testdata
        $statement = new StatementImpl(new VariableImpl(), new VariableImpl(), new VariableImpl());
        
        // assumption is that all given parameter will be returned
        $this->assertEquals(
            array($statement, $this->testGraphUri, array(1)),
            $this->fixture->deleteMatchingStatements($statement, $this->testGraphUri, array(1))
        );
    }
     
    // try to call function method without a successor set leads to an exception
    public function testDeleteMatchingStatementsNoSuccessor()
    {
        $this->setExpectedException('\Exception');
        
        $statement = new StatementImpl(new VariableImpl(), new VariableImpl(), new VariableImpl());
        
        $this->fixture->deleteMatchingStatements($statement);
    }
    
    /**
     * Tests getAvailableGraphs
     */
     
    public function testGetAvailableGraphs()
    {
        // set basic store as successor
        $successor = new BasicStore();
        $this->fixture->setChainSuccessor($successor);
        
        // assumption is that all given parameter will be returned
        $this->assertEquals(
            array(),
            $this->fixture->getAvailableGraphs()
        );
    }
     
    // try to call function method without a successor set leads to an exception
    public function testGetAvailableGraphsNoSuccessor()
    {
        $this->setExpectedException('\Exception');
        
        $this->fixture->getAvailableGraphs();
    }
    
    /**
     * Tests get- and setChainSuccessor
     */
     
    public function testGetAndSetChainSuccessor()
    {
        $successor = new BasicStore();
        
        $this->fixture->setChainSuccessor($successor);
        
        $this->assertEquals($successor, $this->fixture->getChainSuccessor());
    }
    
    /**
     * Tests getLog
     * 
     * The following functions tests the log result for certain function calls
     */
    
    // TODO implement this test using @depends
    public function testGetLogAddStatements()
    {
        // set basic store as successor
        $successor = new BasicStore();
        $this->fixture->setChainSuccessor($successor);
        
        // build testdata
        $statement = new StatementImpl(new VariableImpl(), new VariableImpl(), new VariableImpl());
        $statementIterator = new ArrayStatementIteratorImpl(array($statement));
        
        $options = array(1);
        
        $this->fixture->addStatements($statementIterator, $this->testGraphUri, $options);
        
        $this->assertEquals(
            array(
                // that was called by us directly
                array(
                    'method' => 'addStatements',
                    'parameter' => array(
                        'statements' => $statementIterator,
                        'graphUri' => $this->testGraphUri,
                        'options' => $options
                    )
                ),
                array(
                    'method' => 'invalidateByTriplePattern',
                    'parameter' => array(
                        'statements' => $statementIterator,
                        'graphUri' => $this->testGraphUri
                    )
                ),
                array(
                    'method' => 'buildPatternListByStatement',
                    'parameter' => array(
                        'statement' => $statement,
                        'graphUri' => $this->testGraphUri
                    )
                ),
                array(
                    'method' => 'buildPatternListBySPO',
                    'parameter' => array(
                        's' => '*',
                        'p' => '*',
                        'o' => '*',
                        'graphUri' => $this->testGraphUri
                    )
                ),
            ),
            $this->fixture->getLog()
        );
    }
    
    // TODO implement this test using @depends
    public function testGetLogBuildPatternListByStatement()
    {
        $statement = new StatementImpl(
            new NamedNodeImpl('http://a'), 
            new NamedNodeImpl('http://b'), 
            new NamedNodeImpl('http://c')
        );
        
        $this->fixture->buildPatternListByStatement($statement, $this->testGraphUri);
        
        $this->assertEquals(
            array(
                array(
                    'method' => 'buildPatternListByStatement', 
                    'parameter' => array(
                        'statement' => $statement,
                        'graphUri' => $this->testGraphUri
                    )
                ),
                array(
                    'method' => 'buildPatternListBySPO', 
                    'parameter' => array(
                        's' => 'http://a',
                        'p' => 'http://b',
                        'o' => 'http://c',
                        'graphUri' => $this->testGraphUri
                    )
                )
            ),
            $this->fixture->getLog()
        );
    }
    
    // TODO implement this test using @depends
    public function testGetLogDeleteMatchingStatements()
    {
        // set basic store as successor
        $successor = new BasicStore();
        $this->fixture->setChainSuccessor($successor);
        
        // build testdata
        $statement = new StatementImpl(new VariableImpl(), new VariableImpl(), new VariableImpl());
        $statementIterator = new ArrayStatementIteratorImpl(array($statement));        
        $options = array(1);
        
        $this->fixture->deleteMatchingStatements($statement, $this->testGraphUri, $options);
        
        $this->assertEquals(
            array(
                // that was called by us directly
                array(
                    'method' => 'deleteMatchingStatements',
                    'parameter' => array(
                        'statement' => $statement,
                        'graphUri' => $this->testGraphUri,
                        'options' => $options
                    )
                ),
                array(
                    'method' => 'invalidateByTriplePattern',
                    'parameter' => array(
                        'statements' => $statementIterator,
                        'graphUri' => $this->testGraphUri
                    )
                ),
                array(
                    'method' => 'buildPatternListByStatement',
                    'parameter' => array(
                        'statement' => $statement,
                        'graphUri' => $this->testGraphUri
                    )
                ),
                array(
                    'method' => 'buildPatternListBySPO',
                    'parameter' => array(
                        's' => '*',
                        'p' => '*',
                        'o' => '*',
                        'graphUri' => $this->testGraphUri
                    )
                ),
            ),
            $this->fixture->getLog()
        );
    }
    
    // TODO implement this test using @depends
    public function testGetLogGetMatchingStatements()
    {
        // set basic store as successor
        $successor = new BasicStore();
        $this->fixture->setChainSuccessor($successor);
        
        // build testdata
        $statement = new StatementImpl(new VariableImpl('?s'), new VariableImpl('?p'), new VariableImpl('?o'));
        $statementIterator = new ArrayStatementIteratorImpl(array($statement));        
        $options = array(1);
        
        $query = 'SELECT ?s ?p ?o FROM <'. $this->testGraphUri .'> WHERE { ?s ?p ?o }';
        $queryObject = AbstractQuery::initByQueryString($query);
        $queryObject->getQueryParts();
        
        $this->fixture->getMatchingStatements($statement, $this->testGraphUri, $options);
        
        $this->assertEquals(
            array(
                // that was called by us directly
                array(
                    'method' => 'getMatchingStatements',
                    'parameter' => array(
                        'statement' => $statement,
                        'graphUri' => $this->testGraphUri,
                        'options' => $options
                    )
                ),
                array(
                    'method' => 'saveResult',
                    'parameter' => array(
                        'queryObject' => $queryObject,
                        'result' => array(
                            $statement, 
                            $this->testGraphUri,
                            array(1)
                        )
                    )
                ),
                array(
                    'method' => 'invalidateByQuery',
                    'parameter' => array(
                        'queryObject' => $queryObject
                    )
                )
            ),
            $this->fixture->getLog()
        );
    }
    
    // TODO implement this test using @depends
    public function testGetLogGetStoreDescription()
    {
        // set basic store as successor
        $successor = new BasicStore();
        $this->fixture->setChainSuccessor($successor);
        
        $this->fixture->getStoreDescription();
        
        $this->assertEquals(
            array(
                // that was called by us directly
                array('method' => 'getStoreDescription')
            ),
            $this->fixture->getLog()
        );
    }
    
    // TODO implement this test using @depends
    public function testGetLogHasMatchingStatement()
    {
        // set basic store as successor
        $successor = new BasicStore();
        $this->fixture->setChainSuccessor($successor);
        
        // build testdata
        $statement = new StatementImpl(new VariableImpl(), new VariableImpl(), new VariableImpl());
        $statementIterator = new ArrayStatementIteratorImpl(array($statement));        
        $options = array(1);
        
        $this->fixture->hasMatchingStatement($statement, $this->testGraphUri, $options);
        
        $this->assertEquals(
            array(
                // that was called by us directly
                array(
                    'method' => 'hasMatchingStatement',
                    'parameter' => array(
                        'statement' => $statement,
                        'graphUri' => $this->testGraphUri,
                        'options' => $options,
                    )
                )
            ),
            $this->fixture->getLog()
        );
    }
    
    // TODO implement this test using @depends
    public function testGetLogInvalidateByGraphUri()
    {
        /**
         * First create test data and save it via saveResult
         */
        $query = 'SELECT ?s ?p ?o FROM <'. $this->testGraphUri .'> WHERE { ?s ?p ?o }';
        $queryObject = AbstractQuery::initByQueryString($query);
        
        $result = array(1, 2, 3);
        
        $this->fixture->saveResult($queryObject, $result);
        
        /**
         * Invalidate everything via a invalidateByQuery call
         */
        $this->fixture->invalidateByGraphUri($this->testGraphUri);
        
        // check log for method calls done
        $this->assertEquals(
            array(
                array(
                    'method' => 'saveResult',
                    'parameter' => array(
                        'queryObject' => $queryObject,
                        'result' => $result
                    )
                ),
                array(
                    'method' => 'invalidateByQuery',
                    'parameter' => array(
                        'queryObject' => $queryObject
                    )
                ),
                array(
                    'method' => 'invalidateByGraphUri',
                    'parameter' => array(
                        'graphUri' => $this->testGraphUri
                    )
                ),
                array(
                    'method' => 'invalidateByQuery',
                    'parameter' => array(
                        'queryObject' => AbstractQuery::initByQueryString($query)
                    )
                )
            ),
            $this->fixture->getLog()
        );
    }
    
    // TODO implement this test using @depends
    public function testGetLogInvalidateByQuery()
    {
        /**
         * First create test data and save it via saveResult
         */
        $queryObject = AbstractQuery::initByQueryString(
            'SELECT ?s ?p ?o FROM <'. $this->testGraphUri .'> WHERE { ?s ?p ?o }'
        );
        
        $result = array(1, 2, 3);
        
        $this->fixture->saveResult($queryObject, $result);
        
        /**
         * Invalidate everything via a invalidateByQuery call
         */
        $this->fixture->invalidateByQuery($queryObject);
        
        // check log for method calls done
        $this->assertEquals(
            array(
                array(
                    'method' => 'saveResult',
                    'parameter' => array(
                        'queryObject' => $queryObject,
                        'result' => $result
                    )
                ),
                array(
                    'method' => 'invalidateByQuery',
                    'parameter' => array(
                        'queryObject' => $queryObject
                    )
                ),
                array(
                    'method' => 'invalidateByQuery',
                    'parameter' => array(
                        'queryObject' => $queryObject
                    )
                ),
            ),
            $this->fixture->getLog()
        );
    }
    
    // TODO implement this test using @depends
    // tests invalidateByTriplePattern with statement consisting of 3 variables and graph URI given
    public function testGetLogInvalidateByTriplePatternGraph3VariablesAndUriGiven()
    {
        /**
         * First create test data and save it via saveResult
         */
        $query = 'SELECT ?s ?p ?o FROM <'. $this->testGraphUri .'> WHERE { ?s ?p ?o }';
        $queryObject = AbstractQuery::initByQueryString($query);
        
        $result = array(1, 2, 3);
        
        $this->fixture->saveResult($queryObject, $result);
        
        /**
         * Invalidate everything via a invalidateByTriplePattern call
         */
        $statement = new StatementImpl(new VariableImpl(), new VariableImpl(), new VariableImpl());
        $statementIterator = new ArrayStatementIteratorImpl(array($statement));
        
        $this->fixture->invalidateByTriplePattern($statementIterator, $this->testGraphUri);
        
        // check log for method calls done
        $this->assertEquals(
            array(
                array(
                    'method' => 'saveResult',
                    'parameter' => array(
                        'queryObject' => $queryObject,
                        'result' => $result
                    )
                ),
                array(
                    'method' => 'invalidateByQuery',
                    'parameter' => array(
                        'queryObject' => $queryObject
                    )
                ),
                array(
                    'method' => 'invalidateByTriplePattern',
                    'parameter' => array(
                        'statements' => $statementIterator,
                        'graphUri' => $this->testGraphUri
                    )
                ),
                array(
                    'method' => 'buildPatternListByStatement',
                    'parameter' => array(
                        'statement' => $statement,
                        'graphUri' => $this->testGraphUri
                    )
                ),
                array(
                    'method' => 'buildPatternListBySPO',
                    'parameter' => array(
                        's' => '*',
                        'p' => '*',
                        'o' => '*',
                        'graphUri' => $this->testGraphUri
                    )
                ),
                array(
                    'method' => 'invalidateByQuery',
                    'parameter' => array(
                        'queryObject' => AbstractQuery::initByQueryString($query)
                    )
                ),
            ),
            $this->fixture->getLog()
        );
    }
    
    // TODO implement this test using @depends
    public function testGetLogQuery()
    {
        // set basic store as successor
        $successor = new BasicStore();
        $this->fixture->setChainSuccessor($successor);
        
        // build testdata
        $query = 'SELECT ?s ?p ?o FROM <'. $this->testGraphUri .'> WHERE { ?s ?p ?o }';
        $queryObject = AbstractQuery::initByQueryString($query);
        $queryObject->getQueryParts();
        
        $options = array();
        
        $this->fixture->query($query, $options);
        
        // check log for method calls done
        $this->assertEquals(
            array(
                // was directly called by us
                array(
                    'method' => 'query',
                    'parameter' => array(
                        'query' => $query,
                        'options' => $options
                    )
                ),
                // saves query result
                array(
                    'method' => 'saveResult',
                    'parameter' => array(
                        'queryObject' => $queryObject, 
                        'result' => array(
                            new StatementImpl(
                                new VariableImpl('?s'), 
                                new VariableImpl('?p'), 
                                new VariableImpl('?o')
                            ),
                            null,
                            array()
                        )
                    )
                ),
                // invalidate previous saved result, if available
                array(
                    'method' => 'invalidateByQuery',
                    'parameter' => array(
                        'queryObject' => $queryObject
                    )
                ),
            ),
            $this->fixture->getLog()
        );
    }
    
    /**
     * Tests getMatchingStatement
     */
     
    public function testGetMatchingStatementsNamedNodesLiteral()
    {
        // set basic store as successor
        $successor = new BasicStore();
        $this->fixture->setChainSuccessor($successor);
        
        // test data
        $statement = new StatementImpl(
            new NamedNodeImpl('http://s/'),
            new NamedNodeImpl('http://p/'),
            new LiteralImpl('test literal')
        );
        $statementIterator = new ArrayStatementIteratorImpl(array($statement));
        $options = array(1);
        
        // assumption is that all given parameter will be returned
        $this->assertEquals(
            array(
                $statement, $this->testGraphUri, $options 
            ),
            $this->fixture->getMatchingStatements($statement, $this->testGraphUri, $options)
        );
        
        $this->assertEquals(
            array(
                array(
                    'graph_uris' => array($this->testGraphUri => $this->testGraphUri),
                    'query' => 'SELECT ?s ?p ?o FROM <http://localhost/Saft/TestGraph/> '.
                               'WHERE { '.
                                    '?s ?p ?o '.
                                    'FILTER (str(?s) = "'. $statement->getSubject()->getUri() .'") '.
                                    'FILTER (str(?p) = "'. $statement->getPredicate()->getUri() .'") '.
                                    'FILTER (str(?o) = '. $statement->getObject()->getValue() .') '.
                                '}',
                    'result' => array($statement, $this->testGraphUri, $options),
                    'triple_pattern' => array(
                        $this->testGraphUri . $this->separator .'*'. $this->separator .'*'. $this->separator .'*'
                            => $this->testGraphUri . $this->separator .'*'. $this->separator .'*'. $this->separator .'*'
                    )
                )
            ),
            $this->fixture->getLatestQueryCacheContainer()
        );
    }
     
    public function testGetMatchingStatementsVariables()
    {
        // set basic store as successor
        $successor = new BasicStore();
        $this->fixture->setChainSuccessor($successor);
        
        // test data
        $statement = new StatementImpl(new VariableImpl(), new VariableImpl(), new VariableImpl());
        $options = array(1);
        
        // assumption is that all given parameter will be returned
        $this->assertEquals(
            array(
                $statement, $this->testGraphUri, $options 
            ),
            $this->fixture->getMatchingStatements($statement, $this->testGraphUri, $options)
        );
    }
     
    // try to call function method without a successor set leads to an exception
    public function testHasMatchingStatementsNoSuccessor()
    {
        $this->setExpectedException('\Exception');
        
        // test data
        $statement = new StatementImpl(new VariableImpl(), new VariableImpl(), new VariableImpl());
        
        $this->fixture->getMatchingStatements($statement);
    }
    
    /**
     * Tests getStoreDescription
     */
     
    public function testGetStoreDescription()
    {
        // set basic store as successor
        $successor = new BasicStore();
        $this->fixture->setChainSuccessor($successor);
        
        // assumption is that all given parameter will be returned
        $this->assertEquals(
            array(),
            $this->fixture->getStoreDescription()
        );
    }
     
    // try to call function method without a successor set leads to an exception
    public function testGetStoreDescriptionNoSuccessor()
    {
        $this->setExpectedException('\Exception');
        
        $this->fixture->getStoreDescription();
    }
    
    /**
     * Tests hasMatchingStatement
     */
     
    public function testHasMatchingStatement()
    {
        // set basic store as successor
        $successor = new BasicStore();
        $this->fixture->setChainSuccessor($successor);
        
        // test data
        $statement = new StatementImpl(new VariableImpl(), new VariableImpl(), new VariableImpl());
        $options = array(1);
        
        // assumption is that all given parameter will be returned
        $this->assertEquals(
            array(
                $statement, $this->testGraphUri, $options 
            ),
            $this->fixture->hasMatchingStatement($statement, $this->testGraphUri, $options)
        );
    }
     
    // try to call function method without a successor set leads to an exception
    public function testHasMatchingStatementNoSuccessor()
    {
        $this->setExpectedException('\Exception');        
        
        // test data
        $statement = new StatementImpl(new VariableImpl(), new VariableImpl(), new VariableImpl());
        
        $this->fixture->hasMatchingStatement($statement);
    }
    
    /**
     * Tests invalidateByGraphUri
     */
    public function testInvalidateByGraphUri()
    {
        /**
         * First create test data and save it via saveResult
         */
        $queryObject = AbstractQuery::initByQueryString(
            'SELECT ?s ?p ?o FROM <'. $this->testGraphUri .'> WHERE { ?s ?p ?o }'
        );
        
        $result = array(1, 2, 3);
        
        $this->fixture->saveResult($queryObject, $result);
        
        /**
         * Invalidate everything via a invalidateByGraphUri call
         */
        $this->fixture->invalidateByGraphUri($this->testGraphUri);
        
        /**
         * Check that everything was invalidated:
         * - graph URI entry
         * - pattern key entry
         * - query cache container itself
         */
         
        // graph URI entry
        $this->assertNull($this->fixture->getCache()->get($this->testGraphUri));
        
        // pattern key entry
        $this->assertNull(
            $this->fixture->getCache()->get(
                $this->testGraphUri . $this->separator .'*'. $this->separator .'*'. $this->separator .'*'
            )
        );
        
        // query cache container
        $this->assertNull($this->fixture->getCache()->get($queryObject->getQuery()));
    }
    
    /**
     * Tests invalidateByQuery
     */
    public function testInvalidateByQuery()
    {
        /**
         * First create test data and save it via saveResult
         */
        $queryObject = AbstractQuery::initByQueryString(
            'SELECT ?s ?p ?o FROM <'. $this->testGraphUri .'> WHERE { ?s ?p ?o }'
        );
        
        $result = array(1, 2, 3);
        
        $this->fixture->saveResult($queryObject, $result);
        
        /**
         * Invalidate everything via a invalidateByQuery call
         */
        $this->fixture->invalidateByQuery($queryObject);
        
        /**
         * Check that everything was invalidated:
         * - graph URI entry
         * - pattern key entry
         * - query cache container itself
         */
         
        // graph URI entry
        $this->assertNull($this->fixture->getCache()->get($this->testGraphUri));
        
        // pattern key entry
        $this->assertNull(
            $this->fixture->getCache()->get(
                $this->testGraphUri . $this->separator .'*'. $this->separator .'*'. $this->separator .'*'
            )
        );
        
        // query cache container
        $this->assertNull($this->fixture->getCache()->get($queryObject->getQuery()));
    }
    
    /**
     * Tests invalidateByTriplePattern
     */
     
    public function testInvalidateByTriplePatternGraph3VariablesAndUriGiven()
    {
        /**
         * First create test data and save it via saveResult
         */
        $queryObject = AbstractQuery::initByQueryString(
            'SELECT ?s ?p ?o FROM <'. $this->testGraphUri .'> WHERE { ?s ?p ?o }'
        );
        
        $result = array(1, 2, 3);
        
        $this->fixture->saveResult($queryObject, $result);
        
        /**
         * Invalidate everything via a invalidateByTriplePattern call
         */
        $statementIterator = new ArrayStatementIteratorImpl(
            array(new StatementImpl(new VariableImpl(), new VariableImpl(), new VariableImpl()))
        );
        
        $this->fixture->invalidateByTriplePattern($statementIterator, $this->testGraphUri);
        
        /**
         * Check that everything was invalidated:
         * - graph URI entry
         * - pattern key entry
         * - query cache container itself
         */
         
        // graph URI entry
        $this->assertNull($this->fixture->getCache()->get($this->testGraphUri));
        
        // pattern key entry
        $this->assertNull(
            $this->fixture->getCache()->get(
                $this->testGraphUri . '*'. $this->separator .'*'. $this->separator .'*'
            )
        );
        
        // query cache container
        $this->assertNull($this->fixture->getCache()->get($queryObject->getQuery()));
    }
    
    /**
     * Tests query
     */
     
    public function testQuery()
    {
        // set basic store as successor
        $successor = new BasicStore();
        $this->fixture->setChainSuccessor($successor);
        
        // test data
        $query = 'SELECT * FROM <'. $this->testGraphUri .'> WHERE {?s ?p ?o.}';
        $options = array();        
        
        $this->assertEquals(
            array(
                new StatementImpl(new VariableImpl('?s'), new VariableImpl('?p'), new VariableImpl('?o')), 
                null,
                $options
            ),
            $this->fixture->query($query, $options)
        );
    }
     
    public function testQueryNoSuccessor()
    {
        $this->setExpectedException('\Exception');
        
        $this->fixture->query('SELECT * FROM <'. $this->testGraphUri .'> WHERE {?s ?p ?o.}', array());
    }
    
    /**
     * Tests saveResult
     */
    public function testSaveResultCacheEntries()
    {
        $queryObject = AbstractQuery::initByQueryString(
            'SELECT ?s ?p ?o FROM <'. $this->testGraphUri .'> WHERE { ?s ?p ?o }'
        );
        
        $result = array(1, 2, 3);
        
        $this->fixture->saveResult($queryObject, $result);
        
        /**
         * check saved references between graph URIs (from query) and a array of query strings
         */
        $this->assertEquals(
            array($queryObject->getQuery() => $queryObject->getQuery()), 
            $this->fixture->getCache()->get($this->testGraphUri)
        );
        
        /**
         * check saved references between triple pattern (from query) and a array of query strings
         */
        $this->assertEquals(
            array($queryObject->getQuery() => $queryObject->getQuery()), 
            $this->fixture->getCache()->get(
                $this->testGraphUri . $this->separator .'*'. $this->separator .'*'. $this->separator .'*'
            )
        );
        
        /**
         * check saved references between triple pattern (from query) and a array of query strings
         */
        $this->assertEquals(
            array(
                'graph_uris' => array(
                    $this->testGraphUri => $this->testGraphUri
                ),
                'triple_pattern' => array(
                    $this->testGraphUri . $this->separator .'*'. $this->separator .'*'. $this->separator .'*' => 
                        $this->testGraphUri . $this->separator .'*'. $this->separator .'*'. $this->separator .'*',
                ),
                'result' => $result,
                'query' => $queryObject->getQuery(),
            ), 
            $this->fixture->getCache()->get($queryObject->getQuery())
        );
        
        /**
         * check, that upper query cache container was added to latestQueryCacheContainer during saveResult
         */
        $this->assertEquals(
            array(
                array(
                    'graph_uris' => array(
                        $this->testGraphUri => $this->testGraphUri
                    ),
                    'triple_pattern' => array(
                        $this->testGraphUri . $this->separator .'*'. $this->separator .'*'. $this->separator .'*' => 
                            $this->testGraphUri . $this->separator .'*'. $this->separator .'*'. $this->separator .'*',
                    ),
                    'result' => $result,
                    'query' => $queryObject->getQuery(),
                )
            ),
            $this->fixture->getLatestQueryCacheContainer()
        );
    }
}
