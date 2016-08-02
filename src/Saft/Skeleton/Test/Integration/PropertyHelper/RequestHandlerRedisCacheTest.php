<?php

namespace Saft\Skeleton\Test\Integration\PropertyHelper;

class RequestHandlerRedisCacheTest extends AbstractRequestHandlerTest
{
    protected function isTestPossible()
    {
        if (false === extension_loaded('redis')) {
            $this->markTestSkipped('PHP-extension redis is not loaded. Try sudo apt-get install php5-redis');
        }

        try {
            $this->setupCache();
            $this->fixture->getCache()->setItem('saft-' . time(), false);
        } catch (\Exception $e) {
            $this->markTestSkipped($e->getMessage());
            return false;
        }

        parent::setUp();
    }

    protected function setupCache()
    {
        $this->fixture->setupCache(array(
            'name' => 'redis',
            'host' => 'localhost',
            'port' => 6379,
        ));
    }
}
