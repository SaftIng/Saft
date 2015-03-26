<?php

namespace Saft\Backend\PhpArrayCache\Test;

use Saft\Cache\Test\CacheTest;
use Saft\Cache\Cache;

class CachePhpArrayTest extends CacheTest
{

    /**
     *
     */
    public function setUp()
    {
        parent::setUp();
        
        $this->cacheType = 'phparray';
        
        $this->fixture = new Cache($this->config['phparrayCacheConfig']);
    }
}
