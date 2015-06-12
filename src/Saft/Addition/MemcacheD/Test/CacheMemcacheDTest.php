<?php

namespace Saft\Addition\MemcacheD\Test;

use Saft\Cache\CacheFactoryImpl;
use Saft\Cache\Test\AbstractCacheTest;

class CacheMemcacheDTest extends AbstractCacheTest
{
    public function setUp()
    {
        parent::setUp();

        $cacheFactory = new CacheFactoryImpl();

        if (true === isset($this->config['memcachedCacheConfig'])) {
            try {
                $this->fixture = $cacheFactory->createCache($this->config['memcachedCacheConfig']);
            } catch (\Exception $e) {
                $this->markTestSkipped($e->getMessage());
            }

        } else {
            $this->markTestSkipped('Array memcachedCacheConfig is not set in the test-config.yml.');
        }
    }
}
