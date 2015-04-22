<?php
namespace Saft\QueryCache\Test;

use Saft\Cache\Cache;
use Saft\QueryCache\QueryCache;
use Saft\QueryCache\Test\AbstractQueryCacheIntegrationTest;

class QueryCacheFileCacheIntegrationTest extends AbstractQueryCacheIntegrationTest
{
    /**
     * Overrides setUp method of AbstractQueryCacheIntegrationTest.php and loads a certain cache configuration
     * (File).
     */
    public function setUp()
    {
        parent::setUp();

        $this->cache = new Cache($this->config['fileCacheConfig']);

        $this->fixture = new QueryCache($this->cache);
        $this->fixture->getCache()->clean();
        $this->className = 'QueryCacheFileCacheIntegrationTest';

    }
}
