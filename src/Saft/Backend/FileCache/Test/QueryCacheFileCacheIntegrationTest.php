<?php
namespace Saft\Backend\FileCache\Test;

use Saft\Cache\Cache;
use Saft\QueryCache\QueryCache;
use Saft\QueryCache\Test\QueryCacheIntegrationTest;

class QueryCacheFileCacheIntegrationTest extends QueryCacheIntegrationTest
{
    /**
     * Overrides setUp method of QueryCacheIntegrationTest.php and loads a certain cache configuration (File).
     */
    public function setUp()
    {
        parent::setUp();
        
        $this->cache = new Cache($this->config['fileCacheConfig']);

        $this->fixture = new QueryCache($this->cache);
        $this->fixture->getCache()->clean();
    }
}
