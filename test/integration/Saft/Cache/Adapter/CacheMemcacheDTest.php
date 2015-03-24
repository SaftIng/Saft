<?php

namespace Saft\Cache\Adapter;

use Saft\CacheTest;

class CacheMemcacheDTest extends CacheTest
{

    /**
     *
     */
    public function setUp()
    {
        parent::setUp();
        
        $this->cacheType = 'memcached';
        
        $this->fixture = new \Saft\Cache($this->config['memcachedCacheConfig']);
    }
}
