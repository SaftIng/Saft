<?php

namespace Saft\QueryCache\Test;

use Saft\TestCase;
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
        $this->assertNull($this->fixture->getCache()->get($this->testGraphUri . '_*_*_*'));
        
        // query cache container
        $this->assertNull($this->fixture->getCache()->get($queryObject->getQuery()));
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
            $this->fixture->getCache()->get($this->testGraphUri . '_*_*_*')
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
                    $this->testGraphUri .'_*_*_*' => $this->testGraphUri .'_*_*_*'
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
                        $this->testGraphUri .'_*_*_*' => $this->testGraphUri .'_*_*_*'
                    ),
                    'result' => $result,
                    'query' => $queryObject->getQuery(),
                )
            ),
            $this->fixture->getLatestQueryCacheContainer()
        );
    }
}
