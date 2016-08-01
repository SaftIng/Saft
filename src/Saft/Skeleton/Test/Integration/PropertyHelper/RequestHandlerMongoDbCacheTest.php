<?php

namespace Saft\Skeleton\Test\Integration\PropertyHelper;

class RequestHandlerMongoDbCacheTest extends AbstractRequestHandlerTest
{
    public function setUp()
    {
        if (false === extension_loaded('mongo')) {
            $this->markTestSkipped('PHP-extension mongo is not loaded. Try sudo apt-get install php5-mongo');
        }

        parent::setUp();
    }

    public function setupCache()
    {
        $this->fixture->setupCache(array(
            'name' => 'mongodb',
            'host' => 'localhost',
            //'port' => 27017, // nor port for mongodb possible
        ));
    }
}
