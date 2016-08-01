<?php

namespace Saft\Skeleton\Test\Integration\PropertyHelper;

class RequestHandlerRedisCacheTest extends AbstractRequestHandlerTest
{
    public function setUp()
    {
        if (false === extension_loaded('redis')) {
            $this->markTestSkipped('PHP-extension redis is not loaded. Try sudo apt-get install php5-redis');
        }

        parent::setUp();
    }

    public function setupCache()
    {
        $this->fixture->setupCache(array(
            'name' => 'redis',
            'host' => 'localhost',
            'port' => 6379,
        ));
    }
}
