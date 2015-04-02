<?php
namespace Saft\Backend\LocalStore\Test;

use Saft\Backend\LocalStore\Store\LocalStore;

class LocalStoreUnitTest extends \PHPUnit_Framework_TestCase
{
    // Used for temporary base dirs
    private $tempDirectory = null;
    
    public function tearDown()
    {
        if (!is_null($this->tempDirectory)) {
            TestUtil::deleteDirectory($this->tempDirectory);
        }
    }
    
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConstructorChecksBaseDirForNull()
    {
        new LocalStore(null);
    }

    /**
     * @expectedException \Exception
     */
    public function testInitializeChecksIfBaseDirExists()
    {
        $this->tempDirectory = TestUtil::createTempDirectory();
        // Non existing directory
        $nonExisting = $this->tempDirectory . DIRECTORY_SEPARATOR
            . 'NonExisting';
        assert(!is_dir($nonExisting));
        
        $store = new LocalStore($nonExisting);
        // Should fail in order of the non-existing base dir
        $store->initialize();
    }

    public function testInitializeCreatesStoreFile()
    {
        $this->tempDirectory = TestUtil::createTempDirectory();
        $store = new LocalStore($this->tempDirectory);
        $store->initialize();
        $storeFile = $this->tempDirectory . DIRECTORY_SEPARATOR
            . '.store';
        $this->assertTrue(is_file($storeFile));
    }

    public function testIsInitialized()
    {
        $this->tempDirectory = TestUtil::createTempDirectory();
        $store = new LocalStore($this->tempDirectory);
        $this->assertFalse($store->isInitialized());
        $store->initialize();
        $this->assertTrue($store->isInitialized());
    }
}
