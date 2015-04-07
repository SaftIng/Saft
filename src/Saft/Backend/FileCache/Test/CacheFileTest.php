<?php
namespace Saft\Backend\FileCache\Test;

use Saft\Cache\Test\AbstractCacheTest;
use Saft\Cache\Cache;

class CacheFileIntegrationTest extends AbstractCacheTest
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
