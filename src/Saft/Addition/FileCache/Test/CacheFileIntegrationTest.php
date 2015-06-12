<?php

namespace Saft\Addition\FileCache\Test;

use Saft\Cache\CacheFactoryImpl;
use Saft\Cache\Test\AbstractCacheTest;

class CacheFileIntegrationTest extends AbstractCacheTest
{
    public function setUp()
    {
        parent::setUp();

        $cacheFactory = new CacheFactoryImpl();

        if (true === isset($this->config['fileCacheConfig'])) {
            try {
                $this->fixture = $cacheFactory->createCache($this->config['fileCacheConfig']);
            } catch (\Exception $e) {
                $this->markTestSkipped($e->getMessage());
            }

        } else {
            $this->markTestSkipped('Array fileCacheConfig is not set in the test-config.yml.');
        }
    }

    /*
     * Tests checkRequirements
     */

    public function testCheckRequirements()
    {
        // init instance, set cachePath to system's temp dir
        $this->config['cachePath'] = sys_get_temp_dir();

        $cacheFactory = new CacheFactoryImpl();
        $this->fixture = $cacheFactory->createCache($this->config['fileCacheConfig']);

        $this->assertTrue($this->fixture->checkRequirements());
    }
}
