<?php

namespace Saft\Skeleton\Test\Integration\PropertyHelper;

class RequestHandlerFileCacheTest extends AbstractRequestHandlerTest
{
    protected function setupCache()
    {
        try {
            $this->fixture->setupCache(array(
                'name' => 'filesystem',
                'dir' => sys_get_temp_dir()
            ));
        } catch (\Exception $e) {
            $this->markTestSkipped($e->getMessage());
        }
    }
}
