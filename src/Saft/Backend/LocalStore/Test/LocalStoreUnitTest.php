<?php
namespace Saft\Backend\LocalStore\Test;

use Saft\Backend\LocalStore\Store\LocalStore;

class LocalStoreUnitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Exception
     */
    public function testFoo()
    {
        $store = new LocalStore();
        $this->assertTrue(true, "Nonsense test failed");
        // Should fail with \Exception
        $store->getAvailableGraphs();
    }
}
