<?php

namespace Saft\Backend\FileCache\Test;

use Saft\Backend\FileCache\Cache\File;
use Saft\TestCase;
use Symfony\Component\Yaml\Parser;

class CacheFileUnitTest extends TestCase
{
    /**
     *
     */
    public function setUp()
    {
        parent::setUp();
        
        // set path to test dir
        $saftRootDir = dirname(__FILE__) . '/../../../../../';
        $configFilepath = $saftRootDir . 'test-config.yml';

        // check for config file
        if (false === file_exists($configFilepath)) {
            throw new \Exception('test-config.yml missing');
        }

        // parse YAML file
        $yaml = new Parser();
        $this->config = $yaml->parse(file_get_contents($configFilepath));
        
        $this->fixture = new File();
    }
    
    /**
     * Tests checkRequirements
     */
     
    public function testCheckRequirements()
    {
        // init instance, set cachePath to system's temp dir
        $this->config['cachePath'] = sys_get_temp_dir();
        $this->fixture->setup($this->config);
        
        $this->assertTrue($this->fixture->checkRequirements());
    }
     
    public function testCheckRequirementsCacheFolderNotWriteable()
    {
        // expect exception, because cache folder is invalid
        $this->setExpectedException('\Exception');
        
        // init instance with config from test-config.yml, but override cachePath with invalid value
        $this->config['cachePath'] = time();
        $this->fixture->setup($this->config);
        
        $this->assertFalse($this->fixture->checkRequirements());
    }
}
