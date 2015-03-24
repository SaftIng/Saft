<?php

namespace Saft\Store;

use Saft\Cache;

class QueryCacheFileCacheIntegrationTest extends QueryCacheIntegrationTest
{
    /**
     * Overrides setUp method of QueryCacheIntegrationTest.php and loads a certain cache configuration (File).
     */
    public function setUp()
    {
        parent::setUp();
        
        $this->cache = new Cache($this->config['fileCacheConfig']);

        $this->fixture = new QueryCache($this->cache);
        $this->fixture->getCache()->clean();
    }
}
