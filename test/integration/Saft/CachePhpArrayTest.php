<?php

namespace Saft;

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
