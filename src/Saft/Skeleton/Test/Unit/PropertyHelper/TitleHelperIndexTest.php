<?php

namespace Saft\Skeleton\Test\Unit\PropertyHelper;

use Saft\Skeleton\PropertyHelper\TitleHelperIndex;

class TitleHelperIndexTest extends AbstractIndexTest
{
    public function setUp()
    {
        parent::setUp();
        $this->fixture = new TitleHelperIndex($this->cache, $this->store, $this->testGraph);
    }
}
