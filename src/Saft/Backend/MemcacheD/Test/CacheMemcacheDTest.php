<?php
namespace Saft\Backend\MemcacheD\Test;

use Saft\Cache\Test\AbstractCacheTest;
use Saft\Cache\Cache;

class CacheMemcacheDTest extends AbstractCacheTest
{
    /**
     *
     */
    public function setUp()
    {
        parent::setUp();
        
        $this->cacheType = 'memcached';
        
        $this->fixture = new Cache($this->config['memcachedCacheConfig']);
    }
}
