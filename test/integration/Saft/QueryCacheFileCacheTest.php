<?php

namespace Saft;

class QueryCacheFileCacheTest extends QueryCacheTest
{
    /**
     * Overrides setUp method of QueryCacheTest.php and loads a certain cache configuration (File).
     */
    public function setUp()
    {
        parent::setUp();
        
        $this->cache = new Cache($this->config['fileCacheConfig']);

        $this->fixture = new \Saft\QueryCache($this->cache);
        $this->fixture->getCache()->clean();
    }
}
