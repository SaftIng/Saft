<?php

namespace Saft;

class CacheFileTest extends CacheTest
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
