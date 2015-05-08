<?php

namespace Saft\QueryCache\Test;

use Saft\Cache\CacheFactoryImpl;
use Saft\QueryCache\QueryCache;
use Saft\QueryCache\Test\AbstractQueryCacheIntegrationTest;

// TODO remove that file and test QueryCache only with mock backend
class QueryCacheFileCacheIntegrationTest extends AbstractQueryCacheIntegrationTest
{
    /**
     * Overrides setUp method of AbstractQueryCacheIntegrationTest.php and loads a certain cache configuration
     * (File).
     */
    public function setUp()
    {
        parent::setUp();

        $cacheFactory = new CacheFactoryImpl();
        $this->cache = $cacheFactory->createCache($this->config['fileCacheConfig']);

        $this->fixture = new QueryCache($this->cache);
        $this->fixture->getCache()->clean();
        $this->className = 'QueryCacheFileCacheIntegrationTest';

    }
}
