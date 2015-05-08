<?php

namespace Saft\Backend\MemcacheD\Test;

use Saft\Cache\CacheFactoryImpl;
use Saft\Cache\Test\AbstractCacheTest;

class CacheMemcacheDTest extends AbstractCacheTest
{
    /**
     *
     */
    public function setUp()
    {
        parent::setUp();

        $cacheFactory = new CacheFactoryImpl();
        $this->fixture = $cacheFactory->createCache($this->config['memcachedCacheConfig']);
    }
}
