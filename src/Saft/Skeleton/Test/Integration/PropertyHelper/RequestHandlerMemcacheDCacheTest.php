<?php

namespace Saft\Skeleton\Test\Integration\PropertyHelper;

class RequestHandlerMemcachedCacheTest extends AbstractRequestHandlerTest
{
    public function setUp()
    {
        if (false === extension_loaded('memcached')) {
            $this->markTestSkipped('PHP-extension memcached is not loaded. Try sudo apt-get install php5-memcached');
        }

        parent::setUp();
    }

    public function setupCache()
    {
        $this->fixture->setupCache(array(
            'name' => 'memcached',
            'host' => 'localhost',
            'port' => 11211,
        ));
    }
}
