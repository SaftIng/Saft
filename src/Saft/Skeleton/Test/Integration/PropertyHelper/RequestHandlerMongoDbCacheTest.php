<?php

namespace Saft\Skeleton\Test\Integration\PropertyHelper;

class RequestHandlerMongoDbCacheTest extends AbstractRequestHandlerTest
{
    protected function isTestPossible()
    {
        if (false === extension_loaded('mongo')) {
            $this->markTestSkipped('PHP-extension mongo is not loaded. Try sudo apt-get install php5-mongo');
            return false;
        }

        try {
            $this->setupCache();
            $this->fixture->getCache()->setItem('saft-' . time(), false);
        } catch (\Exception $e) {
            $this->markTestSkipped($e->getMessage());
            return false;
        }

        return true;
    }

    protected function setupCache()
    {
        $this->fixture->setupCache(array(
            'name' => 'mongodb',
            'host' => 'localhost',
            //'port' => 27017, // nor port for mongodb possible
        ));
    }
}
