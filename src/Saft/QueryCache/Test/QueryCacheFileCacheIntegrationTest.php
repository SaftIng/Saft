<?php

namespace Saft\QueryCache\Test;

use Saft\Cache\CacheFactoryImpl;
use Saft\QueryCache\QueryCache;
use Saft\QueryCache\Test\AbstractQueryCacheIntegrationTest;
use Saft\Sparql\Query\QueryFactoryImpl;

// TODO remove that file and test QueryCache only with mock backend
class QueryCacheFileCacheIntegrationTest extends AbstractQueryCacheIntegrationTest
{
    /**
     * Overrides setUp method of AbstractQueryCacheIntegrationTest.php and loads a certain cache configuration
     * (File).
     */
    public function setUp()
    {
        parent::setUp();

        $this->fixture = new QueryCache(
            new CacheFactoryImpl(),
            new QueryFactoryImpl(),
            $this->config['queryCacheConfig']
        );
        $this->fixture->getCache()->clean();
    }
}
