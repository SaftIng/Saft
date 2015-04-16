<?php

namespace Saft\Store\Test;

use Saft\TestCase;
use Saft\Backend\Virtuoso\Store\Virtuoso;
use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\LiteralImpl;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\StatementImpl;
use Saft\Rdf\VariableImpl;
use Saft\Store\StoreChain;
use Saft\Store\Result\StatementResult;
use Symfony\Component\Yaml\Parser;

class StoreChainIntegrationTest extends TestCase
{
    /**
     *
     */
    public function setUp()
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
        $this->config = $yaml->parse(file_get_contents($configFilepath));
        
        $this->fixture = new StoreChain();
    }
    
    /**
     *
     */
    public function tearDown()
    {
        parent::tearDown();
    }

    /**
     * Tests addStatements
     */

    public function testAddStatementsChainQueryCache()
    {
        /**
         * check for configuration entries; if they are not set, skip test
         */
        if (false === isset($this->config['queryCacheConfig'])) {
            $this->markTestSkipped('Array queryCacheConfig is not set in the config.yml.');
            return;
        }
        
        $this->setExpectedException('\Exception');
        
        $this->fixture->setupChain(array($this->config['queryCacheConfig']));
        
        $this->fixture->addStatements(new ArrayStatementIteratorImpl(array(
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
        )));
    }

    public function testAddStatementsChainQueryCacheAndVirtuoso()
    {
        /**
         * check for configuration entries; if they are not set, skip test
         */
        if (false === isset($this->config['queryCacheConfig'])) {
            $this->markTestSkipped('Array queryCacheConfig is not set in the config.yml.');
            return;
        } elseif (false === isset($this->config['virtuosoConfig'])) {
            $this->markTestSkipped('Array virtuosoConfig is not set in the config.yml.');
            return;
        }
        
        $this->fixture->setupChain(array($this->config['queryCacheConfig'], $this->config['virtuosoConfig']));
        
        $this->fixture->addStatements(
            new ArrayStatementIteratorImpl(array(
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
            )),
            $this->testGraphUri
        );
        
        $result = $this->fixture->getMatchingStatements(
            new StatementImpl(new VariableImpl(), new VariableImpl(), new VariableImpl()),
            $this->testGraphUri
        );
        
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
        
        // only compare array values and ignore keys, because of the variables they are random
        $this->assertEquals($statementResultToCheckAgainst, $result);
    }

    public function testAddStatementsNoChainEntries()
    {
        $this->setExpectedException('\Exception');
        
        $this->fixture->addStatements(new ArrayStatementIteratorImpl(array()));
    }
    
    /**
     * Tests deleteMatchingStatements
     */
     
    public function testDeleteMatchingStatementsChainQueryCache()
    {
        /**
         * check for configuration entries; if they are not set, skip test
         */
        if (false === isset($this->config['queryCacheConfig'])) {
            $this->markTestSkipped('Array queryCacheConfig is not set in the config.yml.');
            return;
        }
        
        $this->setExpectedException('\Exception');
        
        $this->fixture->setupChain(array($this->config['queryCacheConfig']));
        
        $this->fixture->deleteMatchingStatements(
            new StatementImpl(new VariableImpl(), new VariableImpl(), new VariableImpl())
        );
    }
     
    public function testDeleteMatchingStatementsChainQueryCacheAndVirtuoso()
    {
        /**
         * check for configuration entries; if they are not set, skip test
         */
        if (false === isset($this->config['queryCacheConfig'])) {
            $this->markTestSkipped('Array queryCacheConfig is not set in the config.yml.');
            return;
        } elseif (false === isset($this->config['virtuosoConfig'])) {
            $this->markTestSkipped('Array virtuosoConfig is not set in the config.yml.');
            return;
        }
        
        $this->fixture->setupChain(array($this->config['queryCacheConfig'], $this->config['virtuosoConfig']));
        
        $chainEntries = $this->fixture->getChainEntries();
        
        // clean cache
        $chainEntries[0]->getCache()->clean();
        
        // create graph freshly
        $chainEntries[1]->query('CLEAR GRAPH <'. $this->testGraphUri .'>');
        
        $this->fixture->addStatements(
            new ArrayStatementIteratorImpl(array(
                new StatementImpl(
                    new NamedNodeImpl('http://s/'),
                    new NamedNodeImpl('http://p/'),
                    new NamedNodeImpl('http://o/')
                ),
                new StatementImpl(
                    new NamedNodeImpl('http://s/'),
                    new NamedNodeImpl('http://p/3'),
                    new LiteralImpl('test literal')
                ),
            )),
            $this->testGraphUri
        );
        
        // only compare array values and ignore keys, because of the variables they are random
        $this->assertEquals(
            2,
            $this->fixture->getMatchingStatements(
                new StatementImpl(new VariableImpl(), new VariableImpl(), new VariableImpl()),
                $this->testGraphUri
            )->getEntryCount()
        );
        
        
        $latestResults = $chainEntries[0]->getLatestResults();
        $firstOne = current(array_values($latestResults));
        
        // remove all statements
        $this->fixture->deleteMatchingStatements(
            new StatementImpl(new VariableImpl(), new VariableImpl(), new VariableImpl()),
            $this->testGraphUri
        );
        
        $statementResult = new StatementResult();
        $statementResult->setVariables(array('s', 'p', 'o'));
        
        // check that everything was removed accordingly
        $this->assertEquals(
            $statementResult,
            $this->fixture->getMatchingStatements(
                new StatementImpl(new VariableImpl(), new VariableImpl(), new VariableImpl()),
                $this->testGraphUri
            )
        );
    }
     
    public function testDeleteMatchingStatementsNoChainEntries()
    {
        $this->setExpectedException('\Exception');
        
        $this->fixture->deleteMatchingStatements(
            new StatementImpl(new VariableImpl(), new VariableImpl(), new VariableImpl())
        );
    }

    /**
     * Tests getAvailableGraphs
     */

    public function testGetAvailableGraphsChainQueryCacheAndVirtuoso()
    {
        /**
         * check for configuration entries; if they are not set, skip test
         */
        if (false === isset($this->config['queryCacheConfig'])) {
            $this->markTestSkipped('Array queryCacheConfig is not set in the config.yml.');
            return;
        } elseif (false === isset($this->config['virtuosoConfig'])) {
            $this->markTestSkipped('Array virtuosoConfig is not set in the config.yml.');
            return;
        }
        
        // setup chain: query cache -> virtuoso
        $this->fixture->setupChain(array($this->config['queryCacheConfig'], $this->config['virtuosoConfig']));
        
        /**
         * get available graphs of the chain
         */
        $virtuoso = new Virtuoso($this->config['virtuosoConfig']);
        $query = $virtuoso->sqlQuery(
            'SELECT ID_TO_IRI(REC_GRAPH_IID) AS graph FROM DB.DBA.RDF_EXPLICITLY_CREATED_GRAPH'
        );

        $graphs = array();

        foreach ($query->fetchAll(\PDO::FETCH_ASSOC) as $graph) {
            $graphs[$graph['graph']] = $graph['graph'];
        }
        
        /**
         * check both results
         */
        $this->assertEquals($graphs, $this->fixture->getAvailableGraphs());
    }
    
    /**
     * Tests getMatchingStatements
     */

    public function testGetMatchingStatementsChainQueryCacheCache()
    {
        /**
         * check for configuration entries; if they are not set, skip test
         */
        if (false === isset($this->config['queryCacheConfig'])) {
            $this->markTestSkipped('Array queryCacheConfig is not set in the config.yml.');
            return;
        }
        
        $this->setExpectedException('\Exception');
        
        // setup chain: query cache -> virtuoso
        $this->fixture->setupChain(array($this->config['queryCacheConfig']));
        
        $statement = new StatementImpl(
            new NamedNodeImpl('http://s/'),
            new NamedNodeImpl('http://p/'),
            new VariableImpl()
        );
        $this->fixture->getMatchingStatements($statement, $this->testGraphUri);
    }

    public function testGetMatchingStatementsChainQueryCacheCacheOffAndVirtuoso()
    {
        /**
         * check for configuration entries; if they are not set, skip test
         */
        if (false === isset($this->config['virtuosoConfig'])) {
            $this->markTestSkipped('Array virtuosoConfig is not set in the config.yml.');
            return;
        }
        
        /**
         * Create test data
         */
        $virtuoso = new Virtuoso($this->config['virtuosoConfig']);
        $virtuoso->dropGraph($this->testGraphUri);
        $virtuoso->addGraph($this->testGraphUri);
        $virtuoso->addStatements(
            new ArrayStatementIteratorImpl(array(
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
            )),
            $this->testGraphUri
        );
        
        // setup chain: query cache -> virtuoso
        $this->fixture->setupChain(array($this->config['queryCacheConfig'], $this->config['virtuosoConfig']));
        
        // clean cache
        $chainEntries = $this->fixture->getChainEntries();
        $chainEntries[0]->getCache()->clean();
        
        // check that no cache entry is available for the test query
        $statement = new StatementImpl(
            new NamedNodeImpl('http://s/'),
            new NamedNodeImpl('http://p/'),
            new VariableImpl()
        );
        $statementIterator = new ArrayStatementIteratorImpl(array($statement));
        $this->assertTrue(
            null === $chainEntries[0]->getCache()->get($chainEntries[0]->generateShortId(
                'SELECT * FROM <'. $this->testGraphUri .'> '.
                'WHERE {'. $chainEntries[0]->sparqlFormat($statementIterator) .'}'
            ))
        );
        
        // build statement result to check against
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
        
        $result = $this->fixture->getMatchingStatements($statement, $this->testGraphUri);
        
        /**
         * check both results
         */
        $this->assertEquals($statementResultToCheckAgainst, $result);
    }

    // basically the same function as testGetMatchingStatementsChainQueryCacheCacheOffAndVirtuoso,
    // but cache is used instead of throwing the query on the store.
    public function testGetMatchingStatementsChainQueryCacheCacheOnAndVirtuoso()
    {
        /**
         * check for configuration entries; if they are not set, skip test
         */
        if (false === isset($this->config['virtuosoConfig'])) {
            $this->markTestSkipped('Array virtuosoConfig is not set in the config.yml.');
            return;
        }
        
        /**
         * Create test data
         */
        $virtuoso = new Virtuoso($this->config['virtuosoConfig']);
        $virtuoso->dropGraph($this->testGraphUri);
        $virtuoso->addGraph($this->testGraphUri);
        $virtuoso->addStatements(
            new ArrayStatementIteratorImpl(array(
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
            )),
            $this->testGraphUri
        );
        
        // setup chain: query cache -> virtuoso
        $this->fixture->setupChain(array($this->config['queryCacheConfig'], $this->config['virtuosoConfig']));
        
        // clean cache
        $chainEntries = $this->fixture->getChainEntries();
        $chainEntries[0]->getCache()->clean();
        
        // check that no cache entry is available for the test query
        $statement = new StatementImpl(
            new NamedNodeImpl('http://s/'),
            new NamedNodeImpl('http://p/'),
            new VariableImpl()
        );
        $statementIterator = new ArrayStatementIteratorImpl(array($statement));
        $testQuery = 'SELECT * FROM <'. $this->testGraphUri .'> '.
                     'WHERE {'. $chainEntries[0]->sparqlFormat($statementIterator) .'}';
        $this->assertTrue(
            null === $chainEntries[0]->getCache()->get($chainEntries[0]->generateShortId($testQuery))
        );
        $this->assertEquals(0, count($chainEntries[0]->getLatestResults()));
        
        // call to fill cache
        $firstResult = $this->fixture->getMatchingStatements($statement, $this->testGraphUri);
        
        // call again to use the cache instead of the store
        $statementResult = new StatementResult();
        $statementResult->setVariables(array('s', 'p', 'o'));
        $statementResult->append(
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new NamedNodeImpl('http://o/')
            )
        );
        $statementResult->append(
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new LiteralImpl('test literal')
            )
        );
        
        $cachedResult = $this->fixture->getMatchingStatements($statement, $this->testGraphUri);
        
        $this->assertEquals($statementResult, $cachedResult);
        
        // check count
        $latestQueryCacheEntries = $chainEntries[0]->getLatestResults();
        $this->assertEquals(1, count($latestQueryCacheEntries));
        
        $statementResult = new StatementResult();
        $statementResult->setVariables(array('s', 'p', 'o'));
        $statementResult->append(
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new NamedNodeImpl('http://o/')
            )
        );
        $statementResult->append(
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new LiteralImpl('test literal')
            )
        );
        
        // check result
        $this->assertEquals(
            array(
                array(
                    'graphIds' => array(0 => 'saft-qC-05f6680daccd57a437570d5f59b0e6'),
                    'query' => 'SELECT ?s ?p ?o FROM <http://localhost/Saft/TestGraph/> '.
                               'WHERE { ?s ?p ?o FILTER (str(?s) = "http://s/") FILTER (str(?p) = "http://p/") }',
                    'relatedQueryCacheEntries' => '',
                    'result' => $statementResult,
                    'triplePattern' => array(
                        'saft-qC-05f6680daccd57a437570d5f59b0e6' => array(
                            'saft-qC-05f6680daccd57a437570d5f59b0e6_*_*_*'
                        )
                    )
                )
            ),
            array_values($latestQueryCacheEntries)
        );
    }
    
    /**
     * Tests getStoreDescription
     */
     
    public function testGetStoreDescriptionChainQueryCache()
    {
        /**
         * check for configuration entries; if they are not set, skip test
         */
        if (false === isset($this->config['queryCacheConfig'])) {
            $this->markTestSkipped('Array queryCacheConfig is not set in the config.yml.');
            return;
        }
        
        $this->setExpectedException('\Exception');
                
        $this->fixture->setupChain(array($this->config['queryCacheConfig']));
        
        $this->fixture->getStoreDescription();
        
        // exception because QueryCache does not support getStoreDescription because it is no store.
    }
    
    public function testGetStoreDescriptionChainQueryCacheAndVirtuoso()
    {
        /**
         * check for configuration entries; if they are not set, skip test
         */
        if (false === isset($this->config['queryCacheConfig'])) {
            $this->markTestSkipped('Array queryCacheConfig is not set in the config.yml.');
            return;
        } elseif (false === isset($this->config['virtuosoConfig'])) {
            $this->markTestSkipped('Array virtuosoConfig is not set in the config.yml.');
            return;
        }
        
        $this->fixture->setupChain(array($this->config['queryCacheConfig'], $this->config['virtuosoConfig']));
        
        $this->assertEquals(array(), $this->fixture->getStoreDescription());
    }
    
    public function testGetStoreDescriptionNoChainEntries()
    {
        $this->setExpectedException('\Exception');
        
        $this->fixture->getStoreDescription();
    }
    
    /**
     * Tests hasMatchingStatements
     */
     
    public function testHasMatchingStatementsChainQueryCache()
    {
        /**
         * check for configuration entries; if they are not set, skip test
         */
        if (false === isset($this->config['queryCacheConfig'])) {
            $this->markTestSkipped('Array queryCacheConfig is not set in the config.yml.');
            return;
        }
        
        $this->setExpectedException('\Exception');
        
        $this->fixture->setupChain(array($this->config['queryCacheConfig']));
        
        $this->fixture->hasMatchingStatement(
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new NamedNodeImpl('http://o/')
            ),
            $this->testGraphUri
        );
    }
     
    public function testHasMatchingStatementsChainQueryCacheAndVirtuoso()
    {
        /**
         * check for configuration entries; if they are not set, skip test
         */
        if (false === isset($this->config['queryCacheConfig'])) {
            $this->markTestSkipped('Array queryCacheConfig is not set in the config.yml.');
            return;
        } elseif (false === isset($this->config['virtuosoConfig'])) {
            $this->markTestSkipped('Array virtuosoConfig is not set in the config.yml.');
            return;
        }
        
        // drop and create test graph
        $virtuoso = new Virtuoso($this->config['virtuosoConfig']);
        $virtuoso->dropGraph($this->testGraphUri);
        $virtuoso->addGraph($this->testGraphUri);
        
        $this->fixture->setupChain(array($this->config['queryCacheConfig'], $this->config['virtuosoConfig']));

        // first test for a statement which does not exist
        /*$this->assertFalse(
            $this->fixture->hasMatchingStatement(
                new StatementImpl(
                    new NamedNodeImpl('http://s/not-there' . time()),
                    new NamedNodeImpl('http://p/not-there' . time()),
                    new NamedNodeImpl('http://o/not-there' . time())
                ),
                $this->testGraphUri
            )
        );*/

        /**
         * now test for a statement which does exist
         */
        $virtuoso->addStatements(
            new ArrayStatementIteratorImpl(array(
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
            )),
            $this->testGraphUri
        );
        
        $this->assertTrue(
            $this->fixture->hasMatchingStatement(
                new StatementImpl(
                    new NamedNodeImpl('http://s/'),
                    new NamedNodeImpl('http://p/'),
                    new VariableImpl()
                ),
                $this->testGraphUri
            )
        );
    }
    
    public function testHasMatchingStatementsNoChainEntries()
    {
        $this->setExpectedException('\Exception');
        
        $this->fixture->hasMatchingStatement(
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new NamedNodeImpl('http://o/')
            ),
            $this->testGraphUri
        );
    }
    
    /**
     * Tests query
     */

    public function testQueryChainQueryCacheAndVirtuoso()
    {
        /**
         * check for configuration entries; if they are not set, skip test
         */
        if (false === isset($this->config['virtuosoConfig'])) {
            $this->markTestSkipped('Array virtuosoConfig is not set in the config.yml.');
            return;
        }
        
        /**
         * Create test data
         */
        $virtuoso = new Virtuoso($this->config['virtuosoConfig']);
        $virtuoso->addGraph($this->testGraphUri);
        $virtuoso->addStatements(
            new ArrayStatementIteratorImpl(array(
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
            )),
            $this->testGraphUri
        );
        
        // setup chain: query cache -> virtuoso
        $this->fixture->setupChain(array($this->config['queryCacheConfig'], $this->config['virtuosoConfig']));
        
        $testQuery = 'SELECT ?s ?p ?o FROM <'. $this->testGraphUri .'> WHERE {?s ?p ?o.}';
        
        // clean cache
        $chainEntries = $this->fixture->getChainEntries();
        $chainEntries[0]->getCache()->clean();
        
        // check that no cache entry is available for the test query
        $this->assertTrue(
            null === $chainEntries[0]->getCache()->get($chainEntries[0]->generateShortId($testQuery))
        );
        
        /**
         * check both results
         */
        $result = $this->fixture->query($testQuery);
        $this->assertEquals($virtuoso->query($testQuery), $result);
    }
    
    /**
     * Tests setChainSuccessor
     */

    public function testSetChainSuccessor()
    {
        $this->fixture->setChainSuccessor($this->getMockBuilder('Saft\Store\Store')->getMock());
    }
}
