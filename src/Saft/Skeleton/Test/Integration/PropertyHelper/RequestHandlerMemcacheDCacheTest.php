<?php

namespace Saft\Skeleton\Test\Integration\PropertyHelper;

class RequestHandlerMemcachedCacheTest extends AbstractRequestHandlerTest
{
    protected function isTestPossible()
    {
        if (false === extension_loaded('memcached')) {
            $this->markTestSkipped(
                'PHP-extension memcached is not loaded. Try sudo apt-get install php5-memcached'
            );
            return false;
        }

        try {
            $this->setupCache();
            // explicit test storing an item, because instantiation isn't enough
            $this->fixture->getCache()->setItem('saft-' . time(), false);
        } catch(\Exception $e) {
            $this->markTestSkipped($e->getMessage());
            return false;
        }
        return true;
    }

    protected function setupCache()
    {
        $this->fixture->setupCache(array(
            'name' => 'memcached',
            'host' => 'localhost',
            'port' => 11211,
        ));
    }
}
