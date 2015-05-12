<?php

namespace Saft\QueryCache\Test;

use Saft\QueryCache\QueryCache;
use Saft\QueryCache\Test\AbstractQueryCacheIntegrationTest;
use Saft\Store\StoreFactoryImpl;

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

        $storeFactory = new StoreFactoryImpl();
        $this->fixture = $storeFactory->createInstance($this->config['queryCacheConfig']);
        $this->fixture->getCache()->clean();
    }
}
