<?php

namespace Saft\Skeleton\Test\Integration\PropertyHelper;

class RequestHandlerApcCacheTest extends AbstractRequestHandlerTest
{
    public function setupCache()
    {
        if (false === extension_loaded('apcu') && false === extension_loaded('apc')) {
            $this->markTestSkipped('PHP-extension APC is not loaded. Try sudo apt-get install php-apc');
            return false;
        }

        try {
            $this->fixture->setupCache(array(
                'name' => 'apc'
            ));
        } catch (\Exception $e) {
            $this->markTestSkipped($e->getMessage());
        }

    }
}
