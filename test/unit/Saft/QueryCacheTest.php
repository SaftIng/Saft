<?php 

namespace Saft;

class QueryCacheTest extends \Saft\TestCase
{
    /**
     * Generates a bunch of test data, but it also makes sure that there is nothing
     * already in the cache.
     */
    public function generateTestCacheEntries()
    {
        $query = "SELECT ?s ?p ?o
                    FROM <". $this->_testGraphUri .">
                    FROM <". $this->_testGraphUri ."2>
                   WHERE {
                     <http://rdfs.org/sioc/ns#foo> ?p ?o.
                     ?s <http://www.w3.org/2000/01/rdf-schema#label> \"foo\".
                     FILTER (?o < 40)
                   }";
        $result = array("foo" => 1337);

        $queryId = $this->_fixture->generateShortId($query);
        $this->assertFalse($this->_fixture->getCache()->get($queryId));
        
        /**
         * graph container
         */
        $graphUris = array(
            $this->_testGraphUri, $this->_testGraphUri."2"
        );
        $graphIds = array();
        
        foreach($graphUris as $graphUri) {
            $graphIds[] = $this->_fixture->generateShortId($graphUri);
        }
        
        foreach($graphIds as $graphId) {
            $this->assertFalse($this->_fixture->getCache()->get($graphId));
        }
        
        /**
         * Generate triple pattern array. 
          
           # Example:
           
            "qryCah--f7bc2ce104" => array (
                0 => 'qryCah--f7bc2ce104_*_*_*'
                1 => 'qryCah--f7bc2ce104_*_2e393a3c3c_*'
            )
        
        
            # Behind the scenes (what does each entry mean):
            
            "$graphId (hashed graph URI)" => array (
                0 => '$keyPrefix$graphId (hashed graph URI)_Placeholder_Placeholder_Placeholder'
                1 => '$keyPrefix$graphId (hashed graph URI)_Placeholder_2e393a3c3c_Placeholder'
            )
         *
         */
        $sPO = "_". $this->_fixture->generateShortId("http://rdfs.org/sioc/ns#foo", false) ."_*_*";
        $sLabelFoo = "_*"
                     . "_" . $this->_fixture->generateShortId("http://www.w3.org/2000/01/rdf-schema#label", false)
                     . "_*";
        
        foreach($graphIds as $graphId) {
            $triplePattern[$graphId] = array(
                $graphId . $sPO, 
                $graphId . $sLabelFoo
            );
            
            $this->assertFalse($this->_fixture->getCache()->get($graphId . $sPO));
            $this->assertFalse($this->_fixture->getCache()->get($graphId . $sLabelFoo));
        }
        
        return array(
            "graphIds"          => $graphIds,       // have cache entries
            "graphUris"         => $graphUris,
            "query"             => $query,
            "queryContainer"    => array(
                "relatedQueryCacheEntries" => "",
                "graphIds"                   => $graphIds,
                "query"                      => $query,
                "result"                     => $result,
                "triplePattern"              => $triplePattern 
            ),
            "queryId"           => $queryId,        // has cache entry 
            "result"            => $result,
            "triplePattern"     => $triplePattern   // have cache entries
        );
    }
    
    /**
     *
     */
    public function setUp()
    {
        parent::setUp();
        
        $this->_fixture = new \Saft\QueryCache($this->_cache);
        $this->_fixture->getCache()->clean();
    }
    
    /**
     * existence 
     */
     
    public function testExistence()
    {
        $this->assertTrue(class_exists("\Saft\QueryCache"));
    }
    
    /**
     * function generateShortId
     */
     
    public function testGenerateShortId()
    {
        $str = "foo";
        
        $this->assertEquals(
            $this->_fixture->getKeyPrefix(). substr(hash("sha512", $str), 0, 10), 
            $this->_fixture->generateShortId($str)
        );
    }
    
    /**
     * instanciation 
     */
     
    public function testInstanciation()
    {
        $queryCache = new \Saft\QueryCache($this->_cache);
    }
    
    /**
     * function invalidateByGraphUri
     */
     
    public function testInvalidateByGraphUri()
    {
        $testData = $this->generateTestCacheEntries();
        
        // put test data into the QueryCache
        $this->_fixture->rememberQueryResult(
            $testData["query"], 
            $testData["result"]
        );        
        
        $queryId = $testData["queryId"];
        $this->assertTrue(
            false !== $this->_fixture->getCache()->get($queryId)
        );
        
        /**
         * invalidate test data in QueryCache
         */
        foreach ($testData["graphUris"] as $graphUri) {
            /**
             * Function to test
               ---------------------------------------------------------------- **/
            $this->_fixture->invalidateByGraphUri($graphUri);
            /* ---------------------------------------------------------------- **/
        }
        
        // test, if each graphId cache entry is set to false (means unset)
        foreach ($testData["graphIds"] as $graphId) {
            $this->assertFalse(
                $this->_fixture->getCache()->get($graphId)
            );
        }
        
        // test, if the according query cache entry is set to false (means unset)
        $this->assertFalse(
            $this->_fixture->getCache()->get($testData["queryId"])
        );
        
        // test, if each triple pattern cache entry is set to false (means unset)
        foreach ($testData["triplePattern"] as $triplePattern) {
            foreach ($triplePattern as $patternId) {
                $this->assertFalse(
                    $this->_fixture->getCache()->get($patternId)
                );
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
        $testGraphId = $this->_fixture->generateShortId($this->_testGraphUri);
        
        // data for 2. transaction
        $testQuery2 = "SELECT ?s FROM <". $this->_testGraphUri ."> WHERE {?s ?p ?o.};";
        $testResult2 = "2";
        
        $testGraphUriId = $this->_fixture->generateShortId($this->_testGraphUri);
        
        /**
            Transaction structure is
            
            1. transaction
            2. transaction
            
            both at the same level and do not depend on each other
        */        
        
        /**
         * 1. transaction
         */
        $this->_fixture->startTransaction();
        $this->_fixture->rememberQueryResult(
            $testCacheEntries["query"], 
            $testCacheEntries["result"]
        );
        $this->_fixture->stopTransaction();
        
        /**
         * 2. transaction
         */
        $this->_fixture->startTransaction();
        $this->_fixture->rememberQueryResult($testQuery2, $testResult2);
        $this->_fixture->stopTransaction();
        
        /**
         * Function to test:
         * 
         * Test, that the invalidation of the following query does not lead to 
         * the invalidation of the other query result.
         */
        $this->_fixture->invalidateByQuery($testCacheEntries["query"]);
        
        
        $this->assertFalse(
            $this->_fixture->getCache()->get(
                $this->_fixture->generateShortId($testCacheEntries["query"])
            )
        );
        
        $testQueryCacheEntity2 = $this->_fixture->getCache()->get(
            $this->_fixture->generateShortId($testQuery2)
        );
        
        $this->assertEquals(
            array(
                "relatedQueryCacheEntries" => $testQueryCacheEntity2["relatedQueryCacheEntries"],
                "graphIds" => array (
                    $testGraphUriId
                ),
                "result" => $testResult2,
                "query" => $testQuery2,
                "triplePattern" => array(
                    $testGraphUriId => array(
                        $testGraphUriId . "_*_*_*"
                    )
                )
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
        $this->_fixture->rememberQueryResult(
            $testData["query"], 
            $testData["result"]
        );
        
        /**
         * Function to test
           ---------------------------------------------------------------- **/
        $this->_fixture->invalidateByQuery($testData["query"]);
        /* ---------------------------------------------------------------- **/
        
        // test, if each graphId cache entry is set to false (means unset)
        foreach ($testData["graphIds"] as $graphId) {
            $this->assertFalse(
                $this->_fixture->getCache()->get($graphId)
            );
        }
        
        // test, if the according query cache entry is set to false (means unset)
        $this->assertFalse(
            $this->_fixture->getCache()->get($testData["queryId"])
        );
        
        // test, if each triple pattern cache entry is set to false (means unset)
        foreach ($testData["triplePattern"] as $triplePattern) {
            foreach ($triplePattern as $patternId) {
                $this->assertFalse(
                    $this->_fixture->getCache()->get($patternId)
                );
            }
        }
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
        $this->_fixture->rememberQueryResult(
            $testData["query"], 
            $testData["result"]
        );
        /* ---------------------------------------------------------------- **/
        
        
        /**
         * graph container
         */
        foreach($testData["graphIds"] as $graphId) {
            $this->assertEquals(
                array($testData["queryId"] => $testData["queryId"]),
                $this->_fixture->getCache()->get($graphId)
            );
        }
        
        /**
         * triple pattern
         */
        foreach ($testData["triplePattern"] as $graphId => $triplePattern) {
            foreach ($triplePattern as $pattern) {
                $this->assertEquals(
                    $testData["queryId"],
                    $this->_fixture->getCache()->get($pattern)
                );
            }
        }
        
        /**
         * query container
         */
        $this->assertEquals(
            $testData["queryContainer"],
            $this->_fixture->getCache()->get($testData["queryId"])
        );
    }
    
    /**
     * function startTransaction
     */
     
    public function testStartTransaction_init()
    {
        $this->_fixture->startTransaction();
        
        // get running transactions
        $this->assertEquals(
            array(0 => "active"),
            $this->_fixture->getRunningTransactions()
        );
        
        // active transaction id
        $this->assertEquals(
            0,
            $this->_fixture->getActiveTransaction()
        );
        
        // placed operations
        $this->assertEquals(
            array(0 => array()),
            $this->_fixture->getPlacedOperations()
        );
    }    
    
    /**
     * test sub transactions handling
     * 
     * This function tests the handling of sub transactions. The idea is, that
     * if a transaction is related to another, because its running as its 
     * child or parent, that on its deletion, all related transactions (parents
     * or childs) will be deleted as well.
     */
     
    public function testSubTransactions_focusQueryCacheEntries()
    {
        // data for 1. transaction
        $testCacheEntries = $this->generateTestCacheEntries();
        $testGraphId = $this->_fixture->generateShortId($this->_testGraphUri);
        
        // data for 2. transaction
        $testQuery2 = "SELECT ?s FROM <". $this->_testGraphUri ."> WHERE {?s ?p ?o.};";
        $testResult2 = "2";
        
        // data for 3. transaction
        $testQuery3 = "SELECT ?p FROM <". $this->_testGraphUri ."> WHERE {?s ?p ?o.};";
        $testResult3 = "3";
        
        // data for 4. transaction
        $testQuery4 = "SELECT ?o FROM <". $this->_testGraphUri ."> WHERE {?s ?p ?o.};";
        $testResult4 = "4";
        
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
        $this->_fixture->startTransaction();
        
        
        // do something ...
        $this->assertFalse($this->_fixture->getCache()->get(
            $testCacheEntries["queryId"]
        ));
        $this->_fixture->rememberQueryResult(
            $testCacheEntries["query"], 
            $testCacheEntries["result"]
        );
        $this->assertFalse($this->_fixture->getCache()->get(
            $testCacheEntries["queryId"]
        ));
        
        
            /**
             * 2. transaction (first level)
             */
            $this->_fixture->startTransaction();
            
        
                /**
                 * 3. transaction (second level)
                 */
                $this->_fixture->startTransaction();
            
    
                // do something ...
                $this->assertFalse($this->_fixture->getCache()->get(
                    $this->_fixture->generateShortId($testQuery3)
                ));
                $this->_fixture->rememberQueryResult($testQuery3, $testResult3);
                $this->assertFalse($this->_fixture->getCache()->get(
                    $this->_fixture->generateShortId($testQuery3)
                ));
                
            
                /**
                 * end of 3. transaction
                 */
                $this->_fixture->stopTransaction();
                
                
                // check that the placed operation of the 3. transaction took place
                $this->assertEquals(
                    array(
                        "relatedQueryCacheEntries" => $this->_fixture->getRelatedQueryCacheEntryList(),
                        "graphIds" => array(
                            $testGraphId
                        ),
                        "query" => $testQuery3,
                        "result" => $testResult3,
                        "triplePattern" => array(
                            $testGraphId => array(
                                $testGraphId . "_*_*_*"
                            )
                        )
                    ),              
                    $this->_fixture->getCache()->get(
                        $this->_fixture->generateShortId($testQuery3)
                    )
                );
                
            
            // do something ...
            $this->assertFalse($this->_fixture->getCache()->get($testQuery2));
            $this->_fixture->rememberQueryResult($testQuery2, $testResult2);
            $this->assertFalse($this->_fixture->getCache()->get($testQuery2));
            
            
            /**
             * end of 2. transaction
             */
            $this->_fixture->stopTransaction();
            
            // check that the placed operation of the 2. transaction took place
            $this->assertEquals(
                array(
                    "relatedQueryCacheEntries" => $this->_fixture->getRelatedQueryCacheEntryList(),
                    "graphIds" => array(
                        $testGraphId
                    ),
                    "query" => $testQuery2,
                    "result" => $testResult2,
                    "triplePattern" => array(
                        $testGraphId => array(
                            $testGraphId . "_*_*_*"
                        )
                    )
                ),              
                $this->_fixture->getCache()->get(
                    $this->_fixture->generateShortId($testQuery2)
                )
            );
            
            
            /**
             * start of 4. transaction
             */
            $this->_fixture->startTransaction();
            
            
            // do something ...
            $this->assertFalse($this->_fixture->getCache()->get($testQuery4));
            $this->_fixture->rememberQueryResult($testQuery4, $testResult4);
            $this->assertFalse($this->_fixture->getCache()->get($testQuery4));
                
            
            /**
             * end of 4. transaction
             */
            $this->_fixture->stopTransaction();
            
        // check that the placed operation of the 4. transaction took place
        $this->assertEquals(
            array(
                "relatedQueryCacheEntries" => $this->_fixture->getRelatedQueryCacheEntryList(),
                "graphIds" => array(
                    $testGraphId
                ),
                "query" => $testQuery4,
                "result" => $testResult4,
                "triplePattern" => array(
                    $testGraphId => array(
                        $testGraphId . "_*_*_*"
                    )
                )
            ),              
            $this->_fixture->getCache()->get(
                $this->_fixture->generateShortId($testQuery4)
            )
        );
        
        
        /**
         * end of 1. transaction
         */
        $this->_fixture->stopTransaction();
        
        // check that the placed operation of the 1. transaction took place
        $this->assertEqualsArrays(
            array_merge(
                $testCacheEntries["queryContainer"],
                array(
                    "relatedQueryCacheEntries" => $this->_fixture->getRelatedQueryCacheEntryList()
                )
            ),
            $this->_fixture->getCache()->get($testCacheEntries["queryId"])
        );
        
        
        /**
         * check the QueryCache entries again (because of the adapted field 
         * relatedQueryCacheEntries
         */
        // QueryCache entry of 2. transaction
        $this->assertEqualsArrays(
            array(
                "relatedQueryCacheEntries" => $this->_fixture->getRelatedQueryCacheEntryList(),
                "graphIds" => array($testGraphId),
                "query" => $testQuery2,
                "result" => $testResult2,
                "triplePattern" => array($testGraphId => array($testGraphId . "_*_*_*"))
            ),              
            $this->_fixture->getCache()->get($this->_fixture->generateShortId($testQuery2))
        );
        
        // QueryCache entry of 3. transaction
        $this->assertEqualsArrays(
            array(
                "relatedQueryCacheEntries" => $this->_fixture->getRelatedQueryCacheEntryList(),
                "graphIds" => array($testGraphId),
                "query" => $testQuery3,
                "result" => $testResult3,
                "triplePattern" => array($testGraphId => array($testGraphId . "_*_*_*"))
            ),              
            $this->_fixture->getCache()->get($this->_fixture->generateShortId($testQuery3))
        );
        
        // QueryCache entry of 4. transaction
        $this->assertEqualsArrays(
            array(
                "relatedQueryCacheEntries" => $this->_fixture->getRelatedQueryCacheEntryList(),
                "graphIds" => array($testGraphId),
                "query" => $testQuery4,
                "result" => $testResult4,
                "triplePattern" => array($testGraphId => array($testGraphId . "_*_*_*"))
            ),              
            $this->_fixture->getCache()->get($this->_fixture->generateShortId($testQuery4))
        );
    }
     
    public function testSubTransactions_indirectInvalidationUsingRememberQueryResult()
    {
        // data for 1. transaction
        $testCacheEntries = $this->generateTestCacheEntries();
        $testGraphId = $this->_fixture->generateShortId($this->_testGraphUri);
        
        // data for 2. transaction
        $testQuery2 = "SELECT ?s FROM <". $this->_testGraphUri ."> WHERE {?s ?p ?o.};";
        $testResult2 = "2";
        
        // data for 3. transaction
        $testQuery3 = "SELECT ?p FROM <". $this->_testGraphUri ."> WHERE {?s ?p ?o.};";
        $testResult3 = "3";
        
        // data for 4. transaction
        $testQuery4 = "SELECT ?o FROM <". $this->_testGraphUri ."> WHERE {?s ?p ?o.};";
        $testResult4 = "4";
        
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
        $this->_fixture->startTransaction();
        $this->_fixture->rememberQueryResult($testCacheEntries["query"], $testCacheEntries["result"]);
        
            /**
             * 2. transaction (first level)
             */
            $this->_fixture->startTransaction();
        
                /**
                 * 3. transaction (second level)
                 */
                $this->_fixture->startTransaction();
                $this->_fixture->rememberQueryResult($testQuery3, $testResult3);
                $this->_fixture->stopTransaction();
                
            
            $this->_fixture->rememberQueryResult($testQuery2, $testResult2);
            
            
            /**
             * end of 2. transaction
             */
            $this->_fixture->stopTransaction();
            
            /**
             * 4. transaction
             */
            $this->_fixture->startTransaction();
            $this->_fixture->rememberQueryResult($testQuery4, $testResult4);
            $this->_fixture->stopTransaction();
        
        /**
         * end of 1. transaction
         */
        $this->_fixture->stopTransaction();
        
        
        /**
         * indirect invalidation by "override" an existing QueryCache entry
         */
        $this->_fixture->rememberQueryResult($testQuery2, "new2");
        
        // test 2: check new data
        $this->assertEquals(
            array(
                "relatedQueryCacheEntries" => "",
                "graphIds" => array($testGraphId),
                "query" => $testQuery2,
                "result" => "new2",
                "triplePattern" => array($testGraphId => array($testGraphId . "_*_*_*"))
            ),              
            $this->_fixture->getCache()->get(
                $this->_fixture->generateShortId($testQuery2)
            )
        );
        
        // test 3
        $this->assertFalse(
            $this->_fixture->getCache()->get(
                $this->_fixture->generateShortId($testQuery3)
            )
        );
        
        // test 4
        $this->assertFalse(
            $this->_fixture->getCache()->get(
                $this->_fixture->generateShortId($testQuery4)
            )
        );
    }
     
    public function testSubTransactions_invalidateOneGraphUri()
    {
        // data for 1. transaction
        $testCacheEntries = $this->generateTestCacheEntries();
        $testGraphId = $this->_fixture->generateShortId($this->_testGraphUri);
        
        // data for 2. transaction
        $testQuery2 = "SELECT ?s FROM <". $this->_testGraphUri ."> WHERE {?s ?p ?o.};";
        $testResult2 = "2";
        
        // data for 3. transaction
        $testQuery3 = "SELECT ?p FROM <". $this->_testGraphUri ."> WHERE {?s ?p ?o.};";
        $testResult3 = "3";
        
        // data for 4. transaction
        $testQuery4 = "SELECT ?o FROM <". $this->_testGraphUri ."> WHERE {?s ?p ?o.};";
        $testResult4 = "4";
        
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
        $this->_fixture->startTransaction();
        $this->_fixture->rememberQueryResult($testCacheEntries["query"], $testCacheEntries["result"]);
        
            /**
             * 2. transaction (first level)
             */
            $this->_fixture->startTransaction();
        
                /**
                 * 3. transaction (second level)
                 */
                $this->_fixture->startTransaction();
                $this->_fixture->rememberQueryResult($testQuery3, $testResult3);
                $this->_fixture->stopTransaction();
                
            
            $this->_fixture->rememberQueryResult($testQuery2, $testResult2);
            
            
            /**
             * end of 2. transaction
             */
            $this->_fixture->stopTransaction();
            
            /**
             * 4. transaction
             */
            $this->_fixture->startTransaction();
            $this->_fixture->rememberQueryResult($testQuery4, $testResult4);
            $this->_fixture->stopTransaction();
        
        /**
         * end of 1. transaction
         */
        $this->_fixture->stopTransaction();
        
        
        /**
         * we invalidate one of the used graph URIs, so all other entries have to be
         * invalidated as well
         */
        $this->_fixture->invalidateByGraphUri($this->_testGraphUri);
        
        // test 2
        $this->assertFalse(
            $this->_fixture->getCache()->get(
                $this->_fixture->generateShortId($testQuery2)
            )
        );
        
        // test 3
        $this->assertFalse(
            $this->_fixture->getCache()->get(
                $this->_fixture->generateShortId($testQuery3)
            )
        );
        
        // test 4
        $this->assertFalse(
            $this->_fixture->getCache()->get(
                $this->_fixture->generateShortId($testQuery4)
            )
        );
    }
     
    public function testSubTransactions_invalidateOneQuery()
    {
        // data for 1. transaction
        $testCacheEntries = $this->generateTestCacheEntries();
        $testGraphId = $this->_fixture->generateShortId($this->_testGraphUri);
        
        // data for 2. transaction
        $testQuery2 = "SELECT ?s FROM <". $this->_testGraphUri ."> WHERE {?s ?p ?o};";
        $testResult2 = "2";
        
        // data for 3. transaction
        $testQuery3 = "SELECT ?p FROM <". $this->_testGraphUri ."> WHERE {?s ?p ?o};";
        $testResult3 = "3";
        
        // data for 4. transaction
        $testQuery4 = "SELECT ?o FROM <". $this->_testGraphUri ."> WHERE {?s ?p ?o};";
        $testResult4 = "4";
        
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
        $this->_fixture->startTransaction();
        $this->_fixture->rememberQueryResult($testCacheEntries["query"], $testCacheEntries["result"]);
        
            /**
             * 2. transaction (first level)
             */
            $this->_fixture->startTransaction();
        
                /**
                 * 3. transaction (second level)
                 */
                $this->_fixture->startTransaction();
                $this->_fixture->rememberQueryResult($testQuery3, $testResult3);
                $this->_fixture->stopTransaction();
                
            
            $this->_fixture->rememberQueryResult($testQuery2, $testResult2);
            
            
            /**
             * end of 2. transaction
             */
            $this->_fixture->stopTransaction();
            
            /**
             * 4. transaction
             */
            $this->_fixture->startTransaction();
            $this->_fixture->rememberQueryResult($testQuery4, $testResult4);
            $this->_fixture->stopTransaction();
        
        /**
         * end of 1. transaction
         */
        $this->_fixture->stopTransaction();
        
        
        /**
         * Function to test
         * 
         * we invalidate one of these queries, to test, that all other entries 
         * getting invalidated as well
         */
        $this->_fixture->invalidateByQuery($testQuery2);
        
        
        // test 2
        $this->assertFalse(
            $this->_fixture->getCache()->get(
                $this->_fixture->generateShortId($testQuery2)
            )
        );
        
        // test 3
        $this->assertFalse(
            $this->_fixture->getCache()->get(
                $this->_fixture->generateShortId($testQuery3)
            )
        );
        
        // test 4
        $this->assertFalse(
            $this->_fixture->getCache()->get(
                $this->_fixture->generateShortId($testQuery4)
            )
        );
    }    
     
    public function testSubTransactions_invalidationInsideSubTransaction()
    {
        // data for 1. transaction
        $testCacheEntries = $this->generateTestCacheEntries();
        $testGraphId = $this->_fixture->generateShortId($this->_testGraphUri);
        
        // data for 2. transaction
        $testQuery2 = "SELECT ?s FROM <". $this->_testGraphUri ."> WHERE {?s ?p ?o};";
        $testResult2 = "2";
        
        // data for 3. transaction
        $testQuery3 = "SELECT ?p FROM <". $this->_testGraphUri ."> WHERE {?s ?p ?o};";
        $testResult3 = "3";
        
        // data for 4. transaction
        $testQuery4 = "SELECT ?o FROM <". $this->_testGraphUri ."> WHERE {?s ?p ?o};";
        $testResult4 = "4";
        
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
        $this->_fixture->startTransaction();
        $this->_fixture->rememberQueryResult($testCacheEntries["query"], $testCacheEntries["result"]);
        
            /**
             * 2. transaction (first level)
             */
            $this->_fixture->startTransaction();
        
                /**
                 * 3. transaction (second level)
                 */
                $this->_fixture->startTransaction();
                $this->_fixture->rememberQueryResult($testQuery3, $testResult3);
                $this->_fixture->stopTransaction();
                
            
            $this->_fixture->rememberQueryResult($testQuery2, $testResult2);
            
            
            /**
             * end of 2. transaction
             */
            $this->_fixture->stopTransaction();

            
            /**
             * Function to test:
             * 
             * we invalidate one of these queries, so the QueryCache for test 2
             * and 3 have to be invalidated too.
             */
            $this->_fixture->invalidateByQuery($testQuery2);
            
            
            /**
             * 4. transaction
             */
            $this->_fixture->startTransaction();
            $this->_fixture->rememberQueryResult($testQuery4, $testResult4);
            $this->_fixture->stopTransaction();
        
        /**
         * end of 1. transaction
         */
        $this->_fixture->stopTransaction();
        
        
        // test 2
        $this->assertFalse(
            $this->_fixture->getCache()->get(
                $this->_fixture->generateShortId($testQuery2)
            )
        );
        
        // test 3
        $this->assertFalse(
            $this->_fixture->getCache()->get($this->_fixture->generateShortId($testQuery3))
        );
        
        // test 4
        $this->assertFalse(
            $this->_fixture->getCache()->get($this->_fixture->generateShortId($testQuery4))
        );
        
    }    
    
    /**
     * startTransaction + stopTransaction with invalidateByGraphUri
     */
     
    public function testTransactionStartAndStop_invalidateByGraphUri()
    {
        $testCacheEntries = $this->generateTestCacheEntries();
        
        // put test data into the query cache
        $this->_fixture->rememberQueryResult(
            $testCacheEntries["query"], 
            $testCacheEntries["result"]
        );        
        
        // check, that there are entries in the query cache
        $this->assertEquals(
            $testCacheEntries["queryContainer"],
            $this->_fixture->getCache()->get($testCacheEntries["queryId"])
        );
     
        // this test function checks that the invalidateByGraphUri function works
        // properly in the transaction context
        
        $this->_fixture->startTransaction();
        
        $this->_fixture->invalidateByGraphUri($testCacheEntries["graphUris"][0]);
        
        // check, that there are STILL the same entries in the query cache
        $this->assertEquals(
            $testCacheEntries["queryContainer"],
            $this->_fixture->getCache()->get($testCacheEntries["queryId"])
        );
        
        $this->_fixture->stopTransaction();
        
        // check, that after the transaction was stopped, there is no cache entry
        // according to the given query ID anymore
        $this->assertFalse(
            $this->_fixture->getCache()->get($testCacheEntries["queryId"])
        );
    }
    
    /**
     * startTransaction + stopTransaction with invalidateByGraphUri and 
     * rememberQueryResult
     */
     
    public function testTransactionStartAndStop_invalidateByGraphUriAndRememberQueryResult()
    {
        /**
         * the following data will be used to remember
         */
        $query = "SELECT ?s FROM <" . $this->_testGraphUri . "> WHERE { ?s ?p ?o. }";
        $queryId = $this->_fixture->generateShortId($query);
        $result = array("s" => "foo");
        
        /**
         * the following data will be used for invalidation
         */
        $testCacheEntries = $this->generateTestCacheEntries();
    
        $this->_fixture->rememberQueryResult(
            $testCacheEntries["query"], 
            $testCacheEntries["result"]
        ); 
        
        
        $this->_fixture->startTransaction();
        
        
        // check invalidateByGraphUri
        $this->assertEquals(
            $testCacheEntries["queryContainer"],
            $this->_fixture->getCache()->get($testCacheEntries["queryId"])
        );
        $this->_fixture->invalidateByGraphUri($testCacheEntries["graphUris"][0]);
        $this->assertEquals(
            $testCacheEntries["queryContainer"],
            $this->_fixture->getCache()->get($testCacheEntries["queryId"])
        );
        
        // check rememberQueryResult
        $this->assertFalse($this->_fixture->getCache()->get($queryId));
        $this->_fixture->rememberQueryResult($query, $result);
        $this->assertFalse($this->_fixture->getCache()->get($queryId));

        
        $this->_fixture->stopTransaction();
        
        
        // test data have to be invalidated by invalidateByGraphUri
        $this->assertFalse(
            $this->_fixture->getCache()->get($testCacheEntries["queryId"])
        );
        
        // query related data from the cache has to be available
        $cacheEntry = $this->_fixture->getCache()->get($queryId);
        $this->assertEquals(
            $result,
            $cacheEntry["result"]
        );
    }
    
    /**
     * startTransaction + stopTransaction with invalidateByQuery
     */
     
    public function testTransactionStartAndStop_invalidateByQuery()
    {
        $testCacheEntries = $this->generateTestCacheEntries();
        
        // put test data into the query cache
        $this->_fixture->rememberQueryResult(
            $testCacheEntries["query"], 
            $testCacheEntries["result"]
        );        
        
        // check, that there are entries in the query cache
        $this->assertEquals(
            $testCacheEntries["queryContainer"],
            $this->_fixture->getCache()->get($testCacheEntries["queryId"])
        );
     
        // this test function checks that the invalidateByGraphUri function works
        // properly in the transaction context
        
        $this->_fixture->startTransaction();
        
        $this->_fixture->invalidateByQuery($testCacheEntries["query"]);
        
        // check, that there are STILL the same entries in the query cache
        $this->assertEquals(
            $testCacheEntries["queryContainer"],
            $this->_fixture->getCache()->get($testCacheEntries["queryId"])
        );
        
        $this->_fixture->stopTransaction();
        
        // check, that after the transaction was stopped, there is no cache entry
        // according to the given query ID anymore
        $this->assertFalse(
            $this->_fixture->getCache()->get($testCacheEntries["queryId"])
        );
    }
    
    /**
     * startTransaction + stopTransaction with rememberQueryResult
     */
     
    public function testTransactionStartAndStop_rememberQueryResult()
    {
        $testCacheEntries = $this->generateTestCacheEntries();
        
        $this->_fixture->startTransaction();
        
        // simple check, that there is no cache entry for the given query
        
        $this->assertFalse($this->_fixture->getCache()->get($testCacheEntries["queryId"]));
        
        $this->_fixture->rememberQueryResult($testCacheEntries["query"], $testCacheEntries["result"]);
        
        // after calling the function rememberQueryResult we have to check that 
        // the query cache is STILL clean, means it has no entry according to the 
        // given $query
        
        $this->assertFalse($this->_fixture->getCache()->get($testCacheEntries["queryId"]));
        
        $this->_fixture->stopTransaction();
        
        // after the transaction was stopped, all placed operations were executed,
        // which includes rememberQueryResult, so at this point there are data in 
        // the cache for the query
        
        $cacheEntry = $this->_fixture->getCache()->get($testCacheEntries["queryId"]);
        
        $this->assertEquals(
            $testCacheEntries["result"],
            $cacheEntry["result"]
        );
    }
}
