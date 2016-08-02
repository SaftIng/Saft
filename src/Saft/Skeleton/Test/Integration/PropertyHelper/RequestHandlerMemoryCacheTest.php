<?php

namespace Saft\Skeleton\Test\Integration\PropertyHelper;

class RequestHandlerMemoryCacheTest extends AbstractRequestHandlerTest
{
    public function setupCache()
    {
        $this->fixture->setupCache(array('name' => 'memory'));
    }
}
