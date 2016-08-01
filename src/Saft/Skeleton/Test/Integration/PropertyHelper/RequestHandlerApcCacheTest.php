<?php

namespace Saft\Skeleton\Test\Integration\PropertyHelper;

class RequestHandlerApcCacheTest extends AbstractRequestHandlerTest
{
    public function setUp()
    {
        if (false === extension_loaded('apc')) {
            $this->markTestSkipped('PHP-extension APC is not loaded. Try sudo apt-get install php-apc');
        }

        parent::setUp();
    }

    public function setupCache()
    {
        $this->fixture->setupCache(array(
            'name' => 'apc'
        ));
    }
}
