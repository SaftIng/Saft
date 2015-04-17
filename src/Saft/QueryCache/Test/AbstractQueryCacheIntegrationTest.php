<?php
namespace Saft\QueryCache\Test;

use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\LiteralImpl;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\StatementImpl;
use Saft\Rdf\StatementIterator;
use Saft\Rdf\VariableImpl;
use Saft\Sparql\Query\AbstractQuery;
use Saft\Store\Result\SetResult;
use Saft\Store\Result\StatementResult;
use Symfony\Component\Yaml\Parser;

/**
 * That abstract class provides tests for the QueryCache component. But it will not be executed directly but
 * over subclasses with cache backend as suffix, such as QueryCacheFileCacheTest.php.
 *
 * This way we can run all the tests for different configuration with minimum overhead.
 */
abstract class AbstractQueryCacheIntegrationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Saft\Cache
     */
    protected $cache;
    
    /**
     * @var string
     */
    protected $className;

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
     * Generates a bunch of test data, but it also makes sure that there is nothing
     * already in the cache.
     */
    public function generateTestCacheEntries()
    {
        $query = 'SELECT ?s ?p ?o
                    FROM <' . $this->testGraphUri . '>
                    FROM <' . $this->testGraphUri .'2>
                   WHERE {
                     <http://rdfs.org/sioc/ns#foo> ?p ?o.
                     ?s <http://www.w3.org/2000/01/rdf-schema#label> "foo".
                     FILTER (?o < 40)
                   }';
        $result = new SetResult();
        $result->setVariables(array('s', 'p', 'o'));
        $result->append(
            array(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new LiteralImpl('24'),
            )
        );
        $result->append(
            array(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/2'),
                new LiteralImpl('23'),
            )
        );

        $queryId = $this->fixture->generateShortId($query);
        $this->assertNull($this->fixture->getCache()->get($queryId));

        /**
         * graph container
         */
        $graphUris = array(
            $this->testGraphUri, $this->testGraphUri . '2'
        );
        $graphIds = array();

        foreach ($graphUris as $graphUri) {
            $graphIds[] = $this->fixture->generateShortId($graphUri);
        }

        foreach ($graphIds as $graphId) {
            $this->assertNull($this->fixture->getCache()->get($graphId));
        }

        /**
         * Generate triple pattern array.

           # Example:

            'qryCah--f7bc2ce104' => array (
                0 => 'qryCah--f7bc2ce104_*_*_*'
                1 => 'qryCah--f7bc2ce104_*_2e393a3c3c_*'
            )


            # Behind the scenes (what does each entry mean):

            '$graphId (hashed graph URI)' => array (
                0 => '$keyPrefix$graphId (hashed graph URI)_Placeholder_Placeholder_Placeholder'
                1 => '$keyPrefix$graphId (hashed graph URI)_Placeholder_2e393a3c3c_Placeholder'
            )
         *
         */
        $sPO = '_'. $this->fixture->generateShortId('http://rdfs.org/sioc/ns#foo', false) .'_*_*';
        $sLabelFoo = '_*'
                     . '_' . $this->fixture->generateShortId('http://www.w3.org/2000/01/rdf-schema#label', false)
                     . '_*';

        foreach ($graphIds as $graphId) {
            $triplePattern[$graphId] = array(
                $graphId . $sPO,
                $graphId . $sLabelFoo
            );

            $this->assertNull($this->fixture->getCache()->get($graphId . $sPO));
            $this->assertNull($this->fixture->getCache()->get($graphId . $sLabelFoo));
        }

        return array(
            'graphIds'          => $graphIds,       // have cache entries
            'graphUris'         => $graphUris,      // related graph URIs (read from the query)
            'query'             => $query,          // query itself
            'queryContainer'    => array(
                'relatedQueryCacheEntries' => '',
                'graphIds'                 => $graphIds,
                'query'                    => $query,
                'result'                   => $result,
                'triplePattern'            => $triplePattern
            ),
            'queryId'           => $queryId,        // unique id to identify the cache container behind the query
            'result'            => $result,         // result of the executed query
            'triplePattern'     => $triplePattern   // extracted triple pattern of the query
        );
    }

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
     *
     */
    public function tearDown()
    {
        if (null !== $this->cache) {
            $this->cache->clean();
        }
        
        parent::tearDown();
    }

    /**
     * Tests addStatements
     */

    public function testAddStatements()
    {
        $storeInterfaceMock = $this->getMockBuilder('Saft\Store\StoreInterface')->getMock();
        // creates a subclass of the mock and adds a dummy function
        $class = 'queryCacheMock'. rand(0, 10000);
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
        
        $this->assertEquals(
            new ArrayStatementIteratorImpl(array()),
            $this->fixture->addStatements(new ArrayStatementIteratorImpl(array()))
        );
    }

    public function testAddStatementsNoSuccessor()
    {
        $this->setExpectedException('\Exception');
        
        $this->fixture->addStatements(new ArrayStatementIteratorImpl(array()));
    }
    
    /**
     * Tests deleteMatchingStatements
     */
    
    public function testDeleteMatchingStatements()
    {
        $storeInterfaceMock = $this->getMockBuilder('Saft\Store\StoreInterface')->getMock();
        // creates a subclass of the mock and adds a dummy function
        $class = 'queryCacheMock'. rand(0, 10000);
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
        
        $this->assertEquals($statement, $this->fixture->deleteMatchingStatements($statement));
    }
    
    public function testDeleteMatchingStatementsNoSuccessor()
    {
        $this->setExpectedException('\Exception');
        
        $this->fixture->deleteMatchingStatements(new StatementImpl(
            new VariableImpl(),
            new VariableImpl(),
            new VariableImpl()
        ));
    }

    /**
     * Tests dropGraph
     */

    public function testDropGraph()
    {
        $storeInterfaceMock = $this->getMockBuilder('Saft\Store\StoreInterface')->getMock();
        // creates a subclass of the mock and adds a dummy dropGraph function
        if (false == class_exists('queryCacheMock')) {
            eval(
                'class queryCacheMock extends '. get_class($storeInterfaceMock) .' {
                    public function dropGraph($graphUri) {
                        return null;
                    }
                }'
            );
        }
        
        $this->fixture->setChainSuccessor(new \queryCacheMock());
        
        $this->fixture->dropGraph($this->testGraphUri);
    }
    
    public function testDropGraphNoSuccessor()
    {
        $this->setExpectedException('\Exception');
        
        $this->fixture->dropGraph($this->testGraphUri);
    }

    /**
     * function generateShortId
     */

    public function testGenerateShortId()
    {
        $str = 'foo';

        $this->assertEquals(
            'saft-qC-' . substr(hash('sha256', $str), 0, 30),
            $this->fixture->generateShortId($str)
        );
    }
    
    /**
     * Tests getMatchingStatements
     */
    
    public function testGetMatchingStatements()
    {
        $storeInterfaceMock = $this->getMockBuilder('Saft\Store\StoreInterface')->getMock();
        // creates a subclass of the mock and adds a dummy function
        $class = 'queryCacheMock'. rand(0, 10000);
        $instance = null;
        // TODO simplify that eval call or get rid of it
        // Its purpose is to create a instanciable class which implements StoreInterface. It has a certain
        // function which just return what was given. That was done to avoid working with concrete store
        // backend implementations like Virtuoso.
        eval(
            'class '. $class .' extends '. get_class($storeInterfaceMock) .' {
                public function getMatchingStatements(Saft\Rdf\Statement $statement, $graphUri = null, '.
                    'array $options = array()) {
                    $statementResult = new Saft\Store\Result\StatementResult();
                    $statementResult->setVariables(array("s", "p", "o"));
                    return $statementResult;
                }
            }
            $instance = new '. $class .'();'
        );
        
        $this->fixture->setChainSuccessor($instance);
        
        $statement = new StatementImpl(new VariableImpl(), new VariableImpl(), new VariableImpl());
        
        $statementResultToCheckAgainst = new StatementResult();
        $statementResultToCheckAgainst->setVariables(array('s', 'p', 'o'));
        
        $this->assertEquals($statementResultToCheckAgainst, $this->fixture->getMatchingStatements($statement));
    }
    
    public function testGetMatchingStatementsUseCachedEntry1()
    {
        $storeInterfaceMock = $this->getMockBuilder('Saft\Store\StoreInterface')->getMock();
        // creates a subclass of the mock and adds a dummy function
        $class = 'queryCacheMock'. rand(0, 10000);
        $instance = null;
        // TODO simplify that eval call or get rid of it
        // Its purpose is to create a instanciable class which implements StoreInterface. It has a certain
        // function which just return what was given. That was done to avoid working with concrete store
        // backend implementations like Virtuoso.
        eval(
            'class '. $class .' extends '. get_class($storeInterfaceMock) .' {
                public function getMatchingStatements(Saft\Rdf\Statement $statement, $graphUri = null, '.
                    'array $options = array()) {
                    $statementResult = new Saft\Store\Result\StatementResult();
                    $statementResult->setVariables(array("s", "p", "o"));
                    $statementResult->append(
                        new Saft\Rdf\StatementImpl(
                            new Saft\Rdf\NamedNodeImpl("http://s/"),
                            new Saft\Rdf\NamedNodeImpl("http://p/"),
                            new Saft\Rdf\LiteralImpl("42")
                        )
                    );
                    return $statementResult;
                }
            }
            $instance = new '. $class .'();'
        );
        
        $this->fixture->setChainSuccessor($instance);
        
        $statement = new StatementImpl(new VariableImpl(), new VariableImpl(), new VariableImpl());
        
        // first call; now the cache is filled and the next call should reuse cache item
        $this->fixture->getMatchingStatements($statement, $this->testGraphUri);
        
        $statementResultToCheckAgainst = new StatementResult();
        $statementResultToCheckAgainst->setVariables(array('s', 'p', 'o'));
        $statementResultToCheckAgainst->append(
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new LiteralImpl('42')
            )
        );
        
        // calling getMatchingStatements second time has to lead to a usage of the cached item
        $this->assertEquals(
            $statementResultToCheckAgainst,
            $this->fixture->getMatchingStatements($statement)
        );
    }
    
    public function testGetMatchingStatementsNoSuccessor()
    {
        $this->setExpectedException('\Exception');
        
        $this->fixture->getMatchingStatements(
            new StatementImpl(
                new VariableImpl(),
                new VariableImpl(),
                new VariableImpl()
            )
        );
    }
    
    /**
     * Tests hasMatchingStatement
     */
    
    // TODO fix that, it fucks around with:
    // PHP Fatal error: Cannot redeclare class queryCache_testHasMatchingStatement in
    // /home/k00ni/Documents/Saft/src/Saft/QueryCache/Test/QueryCacheIntegrationTest.php(367) :
    // eval()'d code on line 5

    public function tes1tHasMatchingStatement()
    {
        $storeInterfaceMock = $this->getMockBuilder('Saft\Store\StoreInterface')->getMock();
        // creates a subclass of the mock and adds a dummy function
        $class = 'queryCache_testHasMatchingStatement' . rand(0, 10000);
        $instance = null;
        // TODO simplify that eval call or get rid of it
        // Its purpose is to create a instanciable class which implements StoreInterface. It has a certain
        // function which just return what was given. That was done to avoid working with concrete store
        // backend implementations like Virtuoso.
        eval(
            'class '. $class .' extends '. get_class($storeInterfaceMock) .' {
                public function hasMatchingStatement(Saft\Rdf\Statement $statement, $graphUri = null, '.
                    'array $options = array()) {
                    return new Saft\Rdf\ArrayStatementIteratorImpl(array($statement));
                }
            }
            $instance = new '. $class .'();'
        );
        
        $this->fixture->setChainSuccessor($instance);
        
        $statement = new StatementImpl(new VariableImpl(), new VariableImpl(), new VariableImpl());
        
        $this->assertEquals(
            new ArrayStatementIteratorImpl(array($statement)),
            $this->fixture->hasMatchingStatement($statement)
        );
    }
    
    public function testHasMatchingStatementNoSuccessor()
    {
        $this->setExpectedException('\Exception');
        
        $this->fixture->hasMatchingStatement(new StatementImpl(
            new VariableImpl(),
            new VariableImpl(),
            new VariableImpl()
        ));
    }

    /**
     * function invalidateByGraphUri
     */

    public function testInvalidateByGraphUri()
    {
        $testData = $this->generateTestCacheEntries();

        // put test data into the QueryCache
        $this->fixture->rememberQueryResult(
            $testData['query'],
            $testData['result']
        );

        $queryId = $testData['queryId'];
        $this->assertTrue(
            null !== $this->fixture->getCache()->get($queryId)
        );

        /**
         * invalidate test data in QueryCache
         */
        foreach ($testData['graphUris'] as $graphUri) {
            /**
             * Function to test
               ---------------------------------------------------------------- **/
            $this->fixture->invalidateByGraphUri($graphUri);
            /* ---------------------------------------------------------------- **/
        }

        // test, if each graphId cache entry is set to false (means unset)
        foreach ($testData['graphIds'] as $graphId) {
            $this->assertNull($this->fixture->getCache()->get($graphId));
        }

        // test, if the according query cache entry is set to false (means unset)
        $this->assertNull($this->fixture->getCache()->get($testData['queryId']));

        // test, if each triple pattern cache entry is set to false (means unset)
        foreach ($testData['triplePattern'] as $triplePattern) {
            foreach ($triplePattern as $patternId) {
                $this->assertNull($this->fixture->getCache()->get($patternId));
            }
        }
    }

    /**
     * function testMultipleRootTransactions
     */

    public function testMultipleRootTransactions()
    {
        // data for 1. transaction
        $testCacheEntries = $this->generateTestCacheEntries();
        $testGraphId = $this->fixture->generateShortId($this->testGraphUri);

        // data for 2. transaction
        $testQuery2 = 'SELECT ?s FROM <' . $this->testGraphUri . '> WHERE {?s ?p ?o.};';
        $testResult2 = '2';

        $testGraphUriId = $this->fixture->generateShortId($this->testGraphUri);

        /**
            Transaction structure is

            1. transaction
            2. transaction

            both at the same level and do not depend on each other
        */

        /**
         * 1. transaction
         */
        $this->fixture->startTransaction();
        $this->fixture->rememberQueryResult($testCacheEntries['query'], $testCacheEntries['result']);
        $this->fixture->stopTransaction();

        /**
         * 2. transaction
         */
        $this->fixture->startTransaction();
        $this->fixture->rememberQueryResult($testQuery2, $testResult2);
        $this->fixture->stopTransaction();

        /**
         * Function to test:
         *
         * Test, that the invalidation of the following query does not lead to
         * the invalidation of the other query result.
         */
        $this->fixture->invalidateByQuery($testCacheEntries['query']);


        $this->assertNull(
            $this->fixture->getCache()->get($this->fixture->generateShortId($testCacheEntries['query']))
        );

        $testQueryCacheEntity2 = $this->fixture->getCache()->get($this->fixture->generateShortId($testQuery2));

        $this->assertEquals(
            array(
                'relatedQueryCacheEntries' => $testQueryCacheEntity2['relatedQueryCacheEntries'],
                'graphIds' => array ($testGraphUriId),
                'result' => $testResult2,
                'query' => $testQuery2,
                'triplePattern' => array($testGraphUriId => array($testGraphUriId . '_*_*_*'))
            ),
            $testQueryCacheEntity2
        );
    }

    /**
     * function testInvalidateByQuery
     */

    public function testInvalidateByQuery()
    {
        $testData = $this->generateTestCacheEntries();

        // put test data into the QueryCache
        $this->fixture->rememberQueryResult($testData['query'], $testData['result']);

        /**
         * Function to test
           ---------------------------------------------------------------- **/
        $this->fixture->invalidateByQuery($testData['query']);
        /* ---------------------------------------------------------------- **/

        // test, if each graphId cache entry is set to false (means unset)
        foreach ($testData['graphIds'] as $graphId) {
            $this->assertNull($this->fixture->getCache()->get($graphId));
        }

        // test, if the according query cache entry is set to false (means unset)
        $this->assertNull($this->fixture->getCache()->get($testData['queryId']));

        // test, if each triple pattern cache entry is set to false (means unset)
        foreach ($testData['triplePattern'] as $triplePattern) {
            foreach ($triplePattern as $patternId) {
                $this->assertNull($this->fixture->getCache()->get($patternId));
            }
        }
    }

    /**
     * Tests invalidateByTriplePattern
     */

    public function testInvalidateByTriplePattern()
    {
        $testData = $this->generateTestCacheEntries();
        
        $queryObject = AbstractQuery::initByQueryString($testData['query']);

        // put test data into the QueryCache
        $this->fixture->rememberQueryResult($testData['query'], $testData['result']);

        $statementIterator = new ArrayStatementIteratorImpl(array(
            new StatementImpl(
                new NamedNodeImpl('http://rdfs.org/sioc/ns#foo'),
                new VariableImpl(),
                new VariableImpl()
            )
        ));
        
        
        $this->fixture->invalidateBySubjectResources($statementIterator, $this->testGraphUri);
        

        // test, if each graphId cache entry is not set
        foreach ($testData['graphIds'] as $graphId) {
            $this->assertNull($this->fixture->getCache()->get($graphId));
        }

        // test, if the according query cache entry is not set
        $this->assertNull($this->fixture->getCache()->get($testData['queryId']));

        // test, if each triple pattern cache entry is not set
        foreach ($testData['triplePattern'] as $triplePattern) {
            foreach ($triplePattern as $patternId) {
                $this->assertNull($this->fixture->getCache()->get($patternId));
            }
        }
    }
    
    
    /**
     * Tests query
     */
    
    public function testQuery()
    {
        $storeInterfaceMock = $this->getMockBuilder('Saft\Store\StoreInterface')->getMock();
        // creates a subclass of the mock and adds a dummy function
        $class = 'queryCacheMock'. rand(0, 10000);
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

        $this->assertEquals('ASK { ?s ?p ?o }', $this->fixture->query('ASK { ?s ?p ?o }'));
    }
    
    public function testQueryNoSuccessor()
    {
        $this->setExpectedException('\Exception');
        
        $this->fixture->getMatchingStatements(new StatementImpl(
            new VariableImpl(),
            new VariableImpl(),
            new VariableImpl()
        ));
    }

    /**
     * function rememberQueryResult
     */

    public function testRememberQueryResult()
    {
        $testData = $this->generateTestCacheEntries();


        /**
         * Function to test
           ---------------------------------------------------------------- **/
        $this->fixture->rememberQueryResult($testData['query'], $testData['result']);
        /* ---------------------------------------------------------------- **/

        /**
         * graph container
         */
        $this->assertEquals(
            array($testData['queryId'] => $testData['queryId']),
            $this->fixture->getCache()->get($testData['graphIds'][0])
        );
        $this->assertNull($this->fixture->getCache()->get($testData['graphIds'][1]));

        /**
         * triple pattern
         */
        foreach ($testData['triplePattern'] as $graphId => $triplePattern) {
            foreach ($triplePattern as $pattern) {
                $this->assertEquals($testData['queryId'], $this->fixture->getCache()->get($pattern));
            }
        }

        /**
         * query container
         */
        $this->assertEquals($testData['queryContainer'], $this->fixture->getCache()->get($testData['queryId']));
    }
    
    /**
     * Tests setChainSuccessor
     */

    public function testSetChainSuccessor()
    {
        $this->fixture->setChainSuccessor($this->getMockBuilder('Saft\Store\StoreInterface')->getMock());
    }

    /**
     * function startTransaction
     */

    public function testStartTransactionInit()
    {
        $this->fixture->startTransaction();

        // get running transactions
        $this->assertEquals(array(0 => 'active'), $this->fixture->getRunningTransactions());

        // active transaction id
        $this->assertEquals(0, $this->fixture->getActiveTransaction());

        // placed operations
        $this->assertEquals(array(0 => array()), $this->fixture->getPlacedOperations());
    }

    /**
     * test sub transactions handling
     *
     * This function tests the handling of sub transactions. The idea is, that
     * if a transaction is related to another, because its running as its
     * child or parent, that on its deletion, all related transactions (parents
     * or childs) will be deleted as well.
     */

    public function testSubTransactionsFocusQueryCacheEntries()
    {
        // data for 1. transaction
        $testCacheEntries = $this->generateTestCacheEntries();
        $testGraphId = $this->fixture->generateShortId($this->testGraphUri);

        // data for 2. transaction
        $testQuery2 = 'SELECT ?s FROM <' . $this->testGraphUri . '> WHERE {?s ?p ?o.};';
        $testResult2 = '2';

        // data for 3. transaction
        $testQuery3 = 'SELECT ?p FROM <' . $this->testGraphUri . '> WHERE {?s ?p ?o.};';
        $testResult3 = '3';

        // data for 4. transaction
        $testQuery4 = 'SELECT ?o FROM <' . $this->testGraphUri . '> WHERE {?s ?p ?o.};';
        $testResult4 = '4';

        /**
            Transaction structure is

            1. transaction
            ^
            |
            `---, 2. transaction
            |   ^
            |   |
            |   `--- 3. transaction
            |
            `--- 4. transaction
        */


        /**
         * 1. transaction
         */
        $this->fixture->startTransaction();


        // do something ...
        $this->assertNull($this->fixture->getCache()->get($testCacheEntries['queryId']));
        $this->fixture->rememberQueryResult($testCacheEntries['query'], $testCacheEntries['result']);
        $this->assertNull($this->fixture->getCache()->get($testCacheEntries['queryId']));

        /**
         * 2. transaction (first level)
         */
        $this->fixture->startTransaction();


        /**
         * 3. transaction (second level)
         */
        $this->fixture->startTransaction();


        // do something ...
        $this->assertNull($this->fixture->getCache()->get($this->fixture->generateShortId($testQuery3)));
        $this->fixture->rememberQueryResult($testQuery3, $testResult3);
        $this->assertNull($this->fixture->getCache()->get($this->fixture->generateShortId($testQuery3)));


        /**
         * end of 3. transaction
         */
        $this->fixture->stopTransaction();


        // check that the placed operation of the 3. transaction took place
        $this->assertEquals(
            array(
                'relatedQueryCacheEntries' => $this->fixture->getRelatedQueryCacheEntryList(),
                'graphIds' => array($testGraphId),
                'query' => $testQuery3,
                'result' => $testResult3,
                'triplePattern' => array($testGraphId => array($testGraphId . '_*_*_*'))
            ),
            $this->fixture->getCache()->get($this->fixture->generateShortId($testQuery3))
        );


        // do something ...
        $this->assertNull($this->fixture->getCache()->get($testQuery2));
        $this->fixture->rememberQueryResult($testQuery2, $testResult2);
        $this->assertNull($this->fixture->getCache()->get($testQuery2));


        /**
         * end of 2. transaction
         */
        $this->fixture->stopTransaction();

        // check that the placed operation of the 2. transaction took place
        $this->assertEquals(
            array(
                'relatedQueryCacheEntries' => $this->fixture->getRelatedQueryCacheEntryList(),
                'graphIds' => array($testGraphId),
                'query' => $testQuery2,
                'result' => $testResult2,
                'triplePattern' => array($testGraphId => array($testGraphId . '_*_*_*'))
            ),
            $this->fixture->getCache()->get($this->fixture->generateShortId($testQuery2))
        );


        /**
         * start of 4. transaction
         */
        $this->fixture->startTransaction();


        // do something ...
        $this->assertNull($this->fixture->getCache()->get($testQuery4));
        $this->fixture->rememberQueryResult($testQuery4, $testResult4);
        $this->assertNull($this->fixture->getCache()->get($testQuery4));


        /**
         * end of 4. transaction
         */
        $this->fixture->stopTransaction();

        // check that the placed operation of the 4. transaction took place
        $this->assertEquals(
            array(
                'relatedQueryCacheEntries' => $this->fixture->getRelatedQueryCacheEntryList(),
                'graphIds' => array($testGraphId),
                'query' => $testQuery4,
                'result' => $testResult4,
                'triplePattern' => array($testGraphId => array($testGraphId . '_*_*_*'))
            ),
            $this->fixture->getCache()->get($this->fixture->generateShortId($testQuery4))
        );


        /**
         * end of 1. transaction
         */
        $this->fixture->stopTransaction();

        // check that the placed operation of the 1. transaction took place
        $this->assertEquals(
            array_merge(
                $testCacheEntries['queryContainer'],
                array('relatedQueryCacheEntries' => $this->fixture->getRelatedQueryCacheEntryList())
            ),
            $this->fixture->getCache()->get($testCacheEntries['queryId'])
        );


        /**
         * check the QueryCache entries again (because of the adapted field
         * relatedQueryCacheEntries
         */
        // QueryCache entry of 2. transaction
        $this->assertEquals(
            array(
                'relatedQueryCacheEntries' => $this->fixture->getRelatedQueryCacheEntryList(),
                'graphIds' => array($testGraphId),
                'query' => $testQuery2,
                'result' => $testResult2,
                'triplePattern' => array($testGraphId => array($testGraphId . '_*_*_*'))
            ),
            $this->fixture->getCache()->get($this->fixture->generateShortId($testQuery2))
        );

        // QueryCache entry of 3. transaction
        $this->assertEquals(
            array(
                'relatedQueryCacheEntries' => $this->fixture->getRelatedQueryCacheEntryList(),
                'graphIds' => array($testGraphId),
                'query' => $testQuery3,
                'result' => $testResult3,
                'triplePattern' => array($testGraphId => array($testGraphId . '_*_*_*'))
            ),
            $this->fixture->getCache()->get($this->fixture->generateShortId($testQuery3))
        );

        // QueryCache entry of 4. transaction
        $this->assertEquals(
            array(
                'relatedQueryCacheEntries' => $this->fixture->getRelatedQueryCacheEntryList(),
                'graphIds' => array($testGraphId),
                'query' => $testQuery4,
                'result' => $testResult4,
                'triplePattern' => array($testGraphId => array($testGraphId . '_*_*_*'))
            ),
            $this->fixture->getCache()->get($this->fixture->generateShortId($testQuery4))
        );
    }

    public function testSubTransactionsIndirectInvalidationUsingRememberQueryResult()
    {
        // data for 1. transaction
        $testCacheEntries = $this->generateTestCacheEntries();
        $testGraphId = $this->fixture->generateShortId($this->testGraphUri);

        // data for 2. transaction
        $testQuery2 = 'SELECT ?s FROM <' . $this->testGraphUri . '> WHERE {?s ?p ?o.};';
        $testResult2 = '2';

        // data for 3. transaction
        $testQuery3 = 'SELECT ?p FROM <' . $this->testGraphUri . '> WHERE {?s ?p ?o.};';
        $testResult3 = '3';

        // data for 4. transaction
        $testQuery4 = 'SELECT ?o FROM <' . $this->testGraphUri . '> WHERE {?s ?p ?o.};';
        $testResult4 = '4';

        /**
            Transaction structure is

            1. transaction
            ^
            |
            `---, 2. transaction
            |   ^
            |   |
            |   `--- 3. transaction
            |
            `--- 4. transaction
        */


        /**
         * 1. transaction
         */
        $this->fixture->startTransaction();
        $this->fixture->rememberQueryResult($testCacheEntries['query'], $testCacheEntries['result']);

        /**
         * 2. transaction (first level)
         */
        $this->fixture->startTransaction();

        /**
         * 3. transaction (second level)
         */
        $this->fixture->startTransaction();
        $this->fixture->rememberQueryResult($testQuery3, $testResult3);
        $this->fixture->stopTransaction();


        $this->fixture->rememberQueryResult($testQuery2, $testResult2);


        /**
         * end of 2. transaction
         */
        $this->fixture->stopTransaction();

        /**
         * 4. transaction
         */
        $this->fixture->startTransaction();
        $this->fixture->rememberQueryResult($testQuery4, $testResult4);
        $this->fixture->stopTransaction();

        /**
         * end of 1. transaction
         */
        $this->fixture->stopTransaction();


        /**
         * indirect invalidation by 'override' an existing QueryCache entry
         */
        $this->fixture->rememberQueryResult($testQuery2, 'new2');

        // test 2: check new data
        $this->assertEquals(
            array(
                'relatedQueryCacheEntries' => '',
                'graphIds' => array($testGraphId),
                'query' => $testQuery2,
                'result' => 'new2',
                'triplePattern' => array($testGraphId => array($testGraphId . '_*_*_*'))
            ),
            $this->fixture->getCache()->get(
                $this->fixture->generateShortId($testQuery2)
            )
        );

        // test 3
        $this->assertNull($this->fixture->getCache()->get($this->fixture->generateShortId($testQuery3)));

        // test 4
        $this->assertNull($this->fixture->getCache()->get($this->fixture->generateShortId($testQuery4)));
    }

    public function testSubTransactionsInvalidateOneGraphUri()
    {
        // data for 1. transaction
        $testCacheEntries = $this->generateTestCacheEntries();
        $testGraphId = $this->fixture->generateShortId($this->testGraphUri);

        // data for 2. transaction
        $testQuery2 = 'SELECT ?s FROM <' . $this->testGraphUri . '> WHERE {?s ?p ?o.};';
        $testResult2 = '2';

        // data for 3. transaction
        $testQuery3 = 'SELECT ?p FROM <' . $this->testGraphUri . '> WHERE {?s ?p ?o.};';
        $testResult3 = '3';

        // data for 4. transaction
        $testQuery4 = 'SELECT ?o FROM <' . $this->testGraphUri . '> WHERE {?s ?p ?o.};';
        $testResult4 = '4';

        /**
            Transaction structure is

            1. transaction
            ^
            |
            `---, 2. transaction
            |   ^
            |   |
            |   `--- 3. transaction
            |
            `--- 4. transaction
        */


        /**
         * 1. transaction
         */
        $this->fixture->startTransaction();
        $this->fixture->rememberQueryResult($testCacheEntries['query'], $testCacheEntries['result']);

        /**
         * 2. transaction (first level)
         */
        $this->fixture->startTransaction();

        /**
         * 3. transaction (second level)
         */
        $this->fixture->startTransaction();
        $this->fixture->rememberQueryResult($testQuery3, $testResult3);
        $this->fixture->stopTransaction();


        $this->fixture->rememberQueryResult($testQuery2, $testResult2);


        /**
         * end of 2. transaction
         */
        $this->fixture->stopTransaction();

        /**
         * 4. transaction
         */
        $this->fixture->startTransaction();
        $this->fixture->rememberQueryResult($testQuery4, $testResult4);
        $this->fixture->stopTransaction();

        /**
         * end of 1. transaction
         */
        $this->fixture->stopTransaction();


        /**
         * we invalidate one of the used graph URIs, so all other entries have to be
         * invalidated as well
         */
        $this->fixture->invalidateByGraphUri($this->testGraphUri);

        // test 2
        $this->assertNull($this->fixture->getCache()->get($this->fixture->generateShortId($testQuery2)));

        // test 3
        $this->assertNull($this->fixture->getCache()->get($this->fixture->generateShortId($testQuery3)));

        // test 4
        $this->assertNull($this->fixture->getCache()->get($this->fixture->generateShortId($testQuery4)));
    }

    public function testSubTransactionsInvalidateOneQuery()
    {
        // data for 1. transaction
        $testCacheEntries = $this->generateTestCacheEntries();
        $testGraphId = $this->fixture->generateShortId($this->testGraphUri);

        // data for 2. transaction
        $testQuery2 = 'SELECT ?s FROM <' . $this->testGraphUri . '> WHERE {?s ?p ?o.};';
        $testResult2 = '2';

        // data for 3. transaction
        $testQuery3 = 'SELECT ?p FROM <' . $this->testGraphUri . '> WHERE {?s ?p ?o.};';
        $testResult3 = '3';

        // data for 4. transaction
        $testQuery4 = 'SELECT ?o FROM <' . $this->testGraphUri . '> WHERE {?s ?p ?o.};';
        $testResult4 = '4';

        /**
            Transaction structure is

            1. transaction
            ^
            |
            `---, 2. transaction
            |   ^
            |   |
            |   `--- 3. transaction
            |
            `--- 4. transaction
        */


        /**
         * 1. transaction
         */
        $this->fixture->startTransaction();
        $this->fixture->rememberQueryResult($testCacheEntries['query'], $testCacheEntries['result']);

        /**
         * 2. transaction (first level)
         */
        $this->fixture->startTransaction();

        /**
         * 3. transaction (second level)
         */
        $this->fixture->startTransaction();
        $this->fixture->rememberQueryResult($testQuery3, $testResult3);
        $this->fixture->stopTransaction();


        $this->fixture->rememberQueryResult($testQuery2, $testResult2);


        /**
         * end of 2. transaction
         */
        $this->fixture->stopTransaction();

        /**
         * 4. transaction
         */
        $this->fixture->startTransaction();
        $this->fixture->rememberQueryResult($testQuery4, $testResult4);
        $this->fixture->stopTransaction();

        /**
         * end of 1. transaction
         */
        $this->fixture->stopTransaction();


        /**
         * Function to test
         *
         * we invalidate one of these queries, to test, that all other entries
         * getting invalidated as well
         */
        $this->fixture->invalidateByQuery($testQuery2);


        // test 2
        $this->assertNull($this->fixture->getCache()->get($this->fixture->generateShortId($testQuery2)));

        // test 3
        $this->assertNull($this->fixture->getCache()->get($this->fixture->generateShortId($testQuery3)));

        // test 4
        $this->assertNull($this->fixture->getCache()->get($this->fixture->generateShortId($testQuery4)));
    }

    public function testSubTransactionsInvalidationInsideSubTransaction()
    {
        // data for 1. transaction
        $testCacheEntries = $this->generateTestCacheEntries();
        $testGraphId = $this->fixture->generateShortId($this->testGraphUri);

        // data for 2. transaction
        $testQuery2 = 'SELECT ?s FROM <' . $this->testGraphUri . '> WHERE {?s ?p ?o.};';
        $testResult2 = '2';

        // data for 3. transaction
        $testQuery3 = 'SELECT ?p FROM <' . $this->testGraphUri . '> WHERE {?s ?p ?o.};';
        $testResult3 = '3';

        // data for 4. transaction
        $testQuery4 = 'SELECT ?o FROM <' . $this->testGraphUri . '> WHERE {?s ?p ?o.};';
        $testResult4 = '4';

        /**
            Transaction structure is

            1. transaction
            ^
            |
            `---, 2. transaction
            |   ^
            |   |
            |   `--- 3. transaction
            |
            `--- 4. transaction
        */


        /**
         * 1. transaction
         */
        $this->fixture->startTransaction();
        $this->fixture->rememberQueryResult($testCacheEntries['query'], $testCacheEntries['result']);

        /**
         * 2. transaction (first level)
         */
        $this->fixture->startTransaction();

        /**
         * 3. transaction (second level)
         */
        $this->fixture->startTransaction();
        $this->fixture->rememberQueryResult($testQuery3, $testResult3);
        $this->fixture->stopTransaction();


        $this->fixture->rememberQueryResult($testQuery2, $testResult2);


        /**
         * end of 2. transaction
         */
        $this->fixture->stopTransaction();


        /**
         * Function to test:
         *
         * we invalidate one of these queries, so the QueryCache for test 2
         * and 3 have to be invalidated too.
         */
        $this->fixture->invalidateByQuery($testQuery2);


        /**
         * 4. transaction
         */
        $this->fixture->startTransaction();
        $this->fixture->rememberQueryResult($testQuery4, $testResult4);
        $this->fixture->stopTransaction();

        /**
         * end of 1. transaction
         */
        $this->fixture->stopTransaction();


        // test 2
        $this->assertNull($this->fixture->getCache()->get($this->fixture->generateShortId($testQuery2)));

        // test 3
        $this->assertNull($this->fixture->getCache()->get($this->fixture->generateShortId($testQuery3)));

        // test 4
        $this->assertNull($this->fixture->getCache()->get($this->fixture->generateShortId($testQuery4)));

    }

    /**
     * startTransaction + stopTransaction with invalidateByGraphUri
     */

    public function testTransactionStartAndStopInvalidateByGraphUri()
    {
        $testCacheEntries = $this->generateTestCacheEntries();

        // put test data into the query cache
        $this->fixture->rememberQueryResult(
            $testCacheEntries['query'],
            $testCacheEntries['result']
        );

        // check, that there are entries in the query cache
        $this->assertEquals(
            $testCacheEntries['queryContainer'],
            $this->fixture->getCache()->get($testCacheEntries['queryId'])
        );

        // this test function checks that the invalidateByGraphUri function works
        // properly in the transaction context

        $this->fixture->startTransaction();

        $this->fixture->invalidateByGraphUri($testCacheEntries['graphUris'][0]);

        // check, that there are STILL the same entries in the query cache
        $this->assertEquals(
            $testCacheEntries['queryContainer'],
            $this->fixture->getCache()->get($testCacheEntries['queryId'])
        );

        $this->fixture->stopTransaction();

        // check, that after the transaction was stopped, there is no cache entry
        // according to the given query ID anymore
        $this->assertNull(
            $this->fixture->getCache()->get($testCacheEntries['queryId'])
        );
    }

    /**
     * startTransaction + stopTransaction with invalidateByGraphUri and
     * rememberQueryResult
     */

    public function testTransactionStartAndStopInvalidateByGraphUriAndRememberQueryResult()
    {
        /**
         * the following data will be used to remember
         */
        $query = 'SELECT ?s FROM <' . $this->testGraphUri . '> WHERE { ?s ?p ?o. }';
        $queryId = $this->fixture->generateShortId($query);
        $result = array('s' => 'foo');

        /**
         * the following data will be used for invalidation
         */
        $testCacheEntries = $this->generateTestCacheEntries();

        $this->fixture->rememberQueryResult(
            $testCacheEntries['query'],
            $testCacheEntries['result']
        );


        $this->fixture->startTransaction();


        // check invalidateByGraphUri
        $this->assertEquals(
            $testCacheEntries['queryContainer'],
            $this->fixture->getCache()->get($testCacheEntries['queryId'])
        );
        $this->fixture->invalidateByGraphUri($testCacheEntries['graphUris'][0]);
        $this->assertEquals(
            $testCacheEntries['queryContainer'],
            $this->fixture->getCache()->get($testCacheEntries['queryId'])
        );

        // check rememberQueryResult
        $this->assertNull($this->fixture->getCache()->get($queryId));
        $this->fixture->rememberQueryResult($query, $result);
        $this->assertNull($this->fixture->getCache()->get($queryId));


        $this->fixture->stopTransaction();


        // test data have to be invalidated by invalidateByGraphUri
        $this->assertNull($this->fixture->getCache()->get($testCacheEntries['queryId']));

        // query related data from the cache has to be available
        $cacheEntry = $this->fixture->getCache()->get($queryId);
        $this->assertEquals($result, $cacheEntry['result']);
    }

    /**
     * startTransaction + stopTransaction with invalidateByQuery
     */

    public function testTransactionStartAndStopInvalidateByQuery()
    {
        $testCacheEntries = $this->generateTestCacheEntries();

        // put test data into the query cache
        $this->fixture->rememberQueryResult(
            $testCacheEntries['query'],
            $testCacheEntries['result']
        );

        // check, that there are entries in the query cache
        $cacheEntry = $this->fixture->getCache()->get($testCacheEntries['queryId']);
        $this->assertEquals(
            $testCacheEntries['queryContainer'],
            $cacheEntry
        );

        $cacheEntry = $this->fixture->getCache()->get($testCacheEntries['queryId']);

        // this test function checks that the invalidateByGraphUri function works properly in the transaction
        // context

        $this->fixture->startTransaction();
        
        $cacheEntry = $this->fixture->getCache()->get($testCacheEntries['queryId']);
        
        $this->fixture->invalidateByQuery($testCacheEntries['query']);
        
        // check that invalidateByQuery was not effective, only marked as to be executed on stop of the
        // transaction
        $this->assertEquals(
            $testCacheEntries['queryContainer'],
            $this->fixture->getCache()->get($testCacheEntries['queryId'])
        );

        $this->fixture->stopTransaction();

        // check, that after the transaction was stopped, there is no cache entry according to the given query
        // ID anymore
        $this->assertNull(
            $this->fixture->getCache()->get($testCacheEntries['queryId'])
        );
    }

    /**
     * startTransaction + stopTransaction with rememberQueryResult
     */

    public function testTransactionStartAndStopRememberQueryResult()
    {
        $testCacheEntries = $this->generateTestCacheEntries();

        $this->fixture->startTransaction();

        // simple check, that there is no cache entry for the given query

        $this->assertNull($this->fixture->getCache()->get($testCacheEntries['queryId']));

        $this->fixture->rememberQueryResult($testCacheEntries['query'], $testCacheEntries['result']);

        // after calling the function rememberQueryResult we have to check that the query cache is STILL clean,
        // means it has no entry according to the given $query

        $this->assertNull($this->fixture->getCache()->get($testCacheEntries['queryId']));

        $this->fixture->stopTransaction();

        // after the transaction was stopped, all placed operations were executed, which includes
        // rememberQueryResult, so at this point there are data in the cache for the query

        $cacheEntry = $this->fixture->getCache()->get($testCacheEntries['queryId']);

        $this->assertEquals($testCacheEntries['result'], $cacheEntry['result']);
    }
}
