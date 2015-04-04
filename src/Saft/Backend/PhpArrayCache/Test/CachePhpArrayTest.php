<?php
namespace Saft\Backend\PhpArrayCache\Test;

use Saft\Cache\Test\AbstractCacheTest;
use Saft\Cache\Cache;

class CachePhpArrayTest extends AbstractCacheTest
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
