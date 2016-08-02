<?php

namespace Saft\Skeleton\Test\Integration\PropertyHelper;

class RequestHandlerMongoDbCacheTest extends AbstractRequestHandlerTest
{
    protected function setupCache()
    {
        if (false === extension_loaded('mongo')) {
            $this->markTestSkipped('PHP-extension mongo is not loaded. Try sudo apt-get install php5-mongo');
            return false;
            
        } elseif (!class_exists('Mongo') || !class_exists('MongoClient')) {
            $this->markTestSkipped('PHP class Mongo or MongoClient does not exist.');
            return false;
        }

        try {
            $this->fixture->setupCache(array(
                'name' => 'mongodb',
                'host' => 'localhost',
                //'port' => 27017, // nor port for mongodb possible
            ));
            $this->fixture->getCache()->setItem('saft-' . time(), false);
        } catch (\Exception $e) {
            $this->markTestSkipped($e->getMessage());
        }
    }
}
