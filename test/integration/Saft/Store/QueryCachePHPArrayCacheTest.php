<?php

namespace Saft\Store;

use Saft\Cache;

class QueryCachePHPArrayCacheTest extends QueryCacheTest
{
    /**
     * Overrides setUp method of QueryCacheTest.php and loads a certain cache configuration (PHPArray).
     */
    public function setUp()
    {
        parent::setUp();
        
        $this->cache = new Cache($this->config['phparrayCacheConfig']);

        $this->fixture = new QueryCache($this->cache);
        $this->fixture->getCache()->clean();
    }
}
