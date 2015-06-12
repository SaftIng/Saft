<?php

namespace Saft\Addition\QueryCache\Test;

use Saft\Addition\QueryCache\QueryCache;
use Saft\Addition\QueryCache\Test\AbstractQueryCacheIntegrationTest;
use Saft\Cache\CacheFactoryImpl;
use Saft\Rdf\StatementIteratorFactoryImpl;
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
            new StatementIteratorFactoryImpl(),
            $this->config['queryCacheConfig']
        );
        $this->fixture->getCache()->clean();
    }
}
