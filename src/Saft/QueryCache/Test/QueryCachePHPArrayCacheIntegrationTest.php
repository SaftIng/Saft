<?php
namespace Saft\QueryCache\Test;

use Saft\Cache\Cache;
use Saft\QueryCache\Test\AbstractQueryCacheIntegrationTest;
use Saft\QueryCache\QueryCache;

class QueryCachePHPArrayCacheIntegrationTest extends AbstractQueryCacheIntegrationTest
{
    /**
     * Overrides setUp method of AbstractQueryCacheIntegrationTest.php and loads a certain cache configuration
     * (PHPArray).
     */
    public function setUp()
    {
        parent::setUp();
        
        $this->cache = new Cache($this->config['phparrayCacheConfig']);

        $this->fixture = new QueryCache($this->cache);
        $this->fixture->getCache()->clean();
        $this->className = 'QueryCachePHPArrayCacheIntegrationTest';
    }
}
