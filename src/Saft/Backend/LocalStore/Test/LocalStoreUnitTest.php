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

    /**
     * @expectedException \LogicException
     */
    public function testGetAvailableGraphsChecksIfInitialized()
    {
        $this->tempDirectory = TestUtil::createTempDirectory();
        $store = new LocalStore($this->tempDirectory);
        // Must intiailized before
        $store->getAvailableGraphs();
    }

    public function testNoAvailableGraphsAfterInitializingAnEmptyBaseDir()
    {
        $this->tempDirectory = TestUtil::createTempDirectory();
        $store = new LocalStore($this->tempDirectory);
        $store->initialize();
        $graphs = $store->getAvailableGraphs();
        $this->assertEmpty($graphs);
    }

    public function testGetAvailableGraphs()
    {
        $this->tempDirectory = TestUtil::createTempDirectory();
        // .store content with two graphs
        $content =
            "{\n"
            . "    \"mapping\": {\n"
            . "        \"http://localhost:8890/foo\": \"/foaf.nt\",\n"
            . "        \"http://dbpedia.org/data/ireland\": \"/ireland.nt\"\n"
            . "    }\n"
            . "}\n";
        $this->writeStoreFile($this->tempDirectory, $content);

        $store = new LocalStore($this->tempDirectory);
        $store->initialize();
        $graphs = $store->getAvailableGraphs();
        $this->assertContains('http://localhost:8890/foo', $graphs);
        $this->assertContains('http://dbpedia.org/data/ireland', $graphs);
    }

    private static function writeStoreFile($dir, $content)
    {
        $fileName = $dir . DIRECTORY_SEPARATOR . '.store';
        $file = fopen($fileName, 'w');
        if ($file === false) {
            throw new \Exception('Unable to write .store file ' . $fileName);
        }
        fwrite($file, $content);
        fclose($file);
    }
}
