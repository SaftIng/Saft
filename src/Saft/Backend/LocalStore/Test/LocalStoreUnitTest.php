<?php
namespace Saft\Backend\LocalStore\Test;

use Saft\Backend\LocalStore\Store\LocalStore;

class LocalStoreUnitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFoo()
    {
        $this->assertTrue(true, "Nonsense test failed");
        // Should failed
        $store = new LocalStore(null);
        $store->initialize();
    }
}
