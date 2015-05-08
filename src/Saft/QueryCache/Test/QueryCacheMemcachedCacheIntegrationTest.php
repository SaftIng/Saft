<?php

namespace Saft\QueryCache\Test;

use Saft\Cache\Cache;
use Saft\QueryCache\Test\AbstractQueryCacheIntegrationTest;
use Saft\QueryCache\QueryCache;

class QueryCacheMemcacheDCacheIntegrationTest extends AbstractQueryCacheIntegrationTest
{
    /**
     * Overrides setUp method of AbstractQueryCacheIntegrationTest.php and loads a certain cache configuration
     * (MemcacheD).
     */
    public function setUp()
    {
        parent::setUp();

        $this->cache = new Cache($this->config['memcachedCacheConfig']);

        $this->fixture = new QueryCache($this->cache);
        $this->fixture->getCache()->clean();
        $this->className = 'QueryCacheMemcacheDCacheIntegrationTest';
    }
}
