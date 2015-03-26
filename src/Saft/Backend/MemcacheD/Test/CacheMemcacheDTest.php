<?php

namespace Saft\Backend\MemcacheD\Test;

use Saft\Cache\Test\CacheTest;
use Saft\Cache\Cache;

class CacheMemcacheDTest extends CacheTest
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
