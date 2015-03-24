<?php

namespace Saft\Cache\Adapter;

use Saft\CacheTest;

class CacheFileIntegrationTest extends CacheTest
{

    /**
     *
     */
    public function setUp()
    {
        parent::setUp();
        
        $this->cacheType = 'file';
        
        $this->fixture = new \Saft\Cache($this->config['fileCacheConfig']);
    }
}
