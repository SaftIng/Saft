<?php

namespace Saft\Store;

use Saft\QueryCache;
use Saft\Store\SparqlStore\Virtuoso;
use Saft\Store\StoreInterface;
use Symfony\Component\Yaml\Parser;

class StoreChainUnitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Contains an instance of the class to test.
     *
     * @var mixed
     */
    protected $fixture;
    
    /**
     *
     */
    public function setUp()
    {
        // set path to test dir
        $saftRootDir = dirname(__FILE__) . '/../../../';
        $configFilepath = $saftRootDir . 'config.yml';

        // check for config file
        if (false === file_exists($configFilepath)) {
            throw new \Exception('config.yml missing in test/config.yml');
        }

        // parse YAML file
        $yaml = new Parser();
        $this->config = $yaml->parse(file_get_contents($configFilepath));
        
        $this->fixture = new StoreChain();
    }
    
    /**
     *
     */
    public function tearDown()
    {   
        parent::tearDown();
    }

    /**
     * Tests getChainEntries
     */

    public function testGetChainEntries()
    {
        $this->fixture->setupChain(array($this->config['queryCacheConfig'], $this->config['virtuosoConfig']));
        
        $chainEntries = $this->fixture->getChainEntries();
        
        $this->assertTrue($chainEntries[0] instanceof QueryCache);
        $this->assertTrue($chainEntries[1] instanceof Virtuoso);
    }

    /**
     * Tests setupChain
     */

    public function testSetupChainEmptyArray()
    {
        $this->setExpectedException('\Exception');
        
        $this->fixture->setupChain(array());
    }
    
    public function testSetupChainOnlyHttp()
    {
        $this->fixture->setupChain(array($this->config['httpConfig']));
        
        // load configuration and init HTTP instance. no errors expected.
    }

    public function testSetupChainInvalidVirtuosoConfig()
    {
        $this->setExpectedException('\Exception');
        
        $this->fixture->setupChain(array(array(
            'type' => 'virtuoso',
            'username' => 'dba'
            // missing password field
        )));
    }

    public function testSetupChainUnknownType()
    {
        $this->setExpectedException('\Exception');
        
        $this->fixture->setupChain(array(array('type' => 'unknown')));
    }
    
    public function testSetupChainOnlyVirtuoso()
    {
        $this->fixture->setupChain(array($this->config['virtuosoConfig']));
        
        // load configuration and init Virtuoso instance. no errors expected.
    }
    
    public function testSetupChainQueryCacheAndVirtuoso()
    {
        $this->fixture->setupChain(array($this->config['queryCacheConfig'], $this->config['virtuosoConfig']));
        
        // load configuration and init Virtuoso instance. no errors expected.
    }

    /**
     * Tests setupChainEntry
     */

    public function testSetupChainEntryFieldTypeNotSet()
    {
        $this->setExpectedException('\Exception');
        
        $this->fixture->setupChainEntry(array());
    }
    
    public function testSetupChainEntryHttp()
    {
        $this->fixture->setupChainEntry($this->config['httpConfig']);
        
        // load configuration and init HTTP instance. no errors expected.
    }

    public function testSetupChainEntryInvalidVirtuosoConfig()
    {
        $this->setExpectedException('\Exception');
        
        $this->fixture->setupChainEntry(array(
            'type' => 'virtuoso',
            'username' => 'dba'
            // missing password field
        ));
    }

    public function testSetupChainEntryUnknownType()
    {
        $this->setExpectedException('\Exception');
        
        $this->fixture->setupChainEntry(array('type' => 'unknown'));
    }
    
    public function testSetupChainEntryVirtuoso()
    {
        $this->fixture->setupChainEntry($this->config['virtuosoConfig']);
        
        // load configuration and init Virtuoso instance. no errors expected.
    }
}
