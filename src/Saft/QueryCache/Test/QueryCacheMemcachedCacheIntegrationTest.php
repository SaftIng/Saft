<?php

namespace Saft\QueryCache\Test;

use Saft\Cache\CacheFactoryImpl;
use Saft\QueryCache\QueryCache;
use Saft\QueryCache\Test\AbstractQueryCacheIntegrationTest;

class QueryCacheMemcacheDCacheIntegrationTest extends AbstractQueryCacheIntegrationTest
{
    /**
     * Overrides setUp method of AbstractQueryCacheIntegrationTest.php and loads a certain cache configuration
     * (MemcacheD).
     */
    public function setUp()
    {
        parent::setUp();

        $cacheFactory = new CacheFactoryImpl();
        $this->cache = $cacheFactory->createCache($this->config['memcachedCacheConfig']);

        $this->fixture = new QueryCache($this->cache);
        $this->fixture->getCache()->clean();
        $this->className = 'QueryCacheMemcacheDCacheIntegrationTest';
    }
}
