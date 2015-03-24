<?php

namespace Saft\Cache\Adapter;

use Saft\CacheTest;

class CachePhpArrayTest extends CacheTest
{

    /**
     *
     */
    public function setUp()
    {
        parent::setUp();
        
        $this->cacheType = 'phparray';
        
        $this->fixture = new \Saft\Cache($this->config['phparrayCacheConfig']);
    }
}
