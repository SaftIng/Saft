<?php

namespace Saft;

class QueryCacheMemcacheDTest extends QueryCacheTest
{
    /**
     * Overrides setUp method of QueryCacheTest.php and loads a certain cache configuration (MemcacheD).
     */
    public function setUp()
    {
        parent::setUp();
        
        $this->cache = new Cache($this->config['memcachedCacheConfig']);

        $this->fixture = new \Saft\QueryCache($this->cache);
        $this->fixture->getCache()->clean();
    }
}
