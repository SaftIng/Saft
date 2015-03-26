<?php

namespace Saft\Backend\FileCache\Test;

use Saft\Cache\Test\CacheTest;
use Saft\Cache\Cache;

class CacheFileIntegrationTest extends CacheTest
{

    /**
     *
     */
    public function setUp()
    {
        parent::setUp();
        
        $this->cacheType = 'file';
        
        $this->fixture = new Cache($this->config['fileCacheConfig']);
    }
}
