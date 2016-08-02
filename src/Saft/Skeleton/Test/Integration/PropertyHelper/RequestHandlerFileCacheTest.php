<?php

namespace Saft\Skeleton\Test\Integration\PropertyHelper;

class RequestHandlerFileCacheTest extends AbstractRequestHandlerTest
{
    protected function isTestPossible()
    {
        try {
            $this->fixture->setupCache(array(
                'name' => 'filesystem',
                'dir' => sys_get_temp_dir()
            ));
        } catch (\Exception $e) {
            $this->markTestSkipped($e->getMessage());
            return false;
        }
        return true;
    }

    protected function setupCache()
    {
        $this->fixture->setupCache(array(
            'name' => 'filesystem',
            'dir' => sys_get_temp_dir()
        ));
    }
}
