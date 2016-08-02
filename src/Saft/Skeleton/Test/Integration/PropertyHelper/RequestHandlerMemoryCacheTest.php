<?php

namespace Saft\Skeleton\Test\Integration\PropertyHelper;

class RequestHandlerMemoryCacheTest extends AbstractRequestHandlerTest
{
    protected function isTestPossible()
    {
        return true;
    }

    public function setupCache()
    {
        $this->fixture->setupCache(array('name' => 'memory'));
    }
}
