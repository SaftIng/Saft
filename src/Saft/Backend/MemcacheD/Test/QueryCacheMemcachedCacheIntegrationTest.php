<?php
namespace Saft\Backend\MemcacheD\Test;

use Saft\Cache\Cache;
use Saft\QueryCache\Test\QueryCacheIntegrationTest;
use Saft\QueryCache\QueryCache;

class QueryCacheMemcacheDCacheIntegrationTest extends QueryCacheIntegrationTest
{
    /**
     * Overrides setUp method of QueryCacheIntegrationTest.php and loads a certain cache configuration (MemcacheD).
     */
    public function setUp()
    {
        parent::setUp();
        
        $this->cache = new Cache($this->config['memcachedCacheConfig']);

        $this->fixture = new QueryCache($this->cache);
        $this->fixture->getCache()->clean();
    }
}
