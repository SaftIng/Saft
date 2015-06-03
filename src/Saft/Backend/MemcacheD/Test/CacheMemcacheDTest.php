<?php

namespace Saft\Backend\MemcacheD\Test;

use Saft\Cache\CacheFactoryImpl;
use Saft\Cache\Test\AbstractCacheTest;

class CacheMemcacheDTest extends AbstractCacheTest
{
    public function setUp()
    {
        parent::setUp();

        $cacheFactory = new CacheFactoryImpl();

        try {
            $this->fixture = $cacheFactory->createCache($this->config['memcachedCacheConfig']);
        } catch (\Exception $e) {
            $this->markTestSkipped($e->getMessage());
        }
    }
}
