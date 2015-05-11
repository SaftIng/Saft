<?php

namespace Saft\Store\Test;

use Saft\Backend\Virtuoso\Store\Virtuoso;
use Saft\QueryCache\QueryCache;
use Saft\Store\StoreChain;
use Saft\Test\TestCase;
use Symfony\Component\Yaml\Parser;

class StoreChainUnitTest extends TestCase
{
    /**
     *
     */
    public function setUp()
    {
        $this->markTestSkipped('Ignore StoreChain as long its not removed.');

        // set path to test dir
        $saftRootDir = dirname(__FILE__) . '/../../../../';
        $configFilepath = $saftRootDir . 'test-config.yml';

        // check for config file
        if (false === file_exists($configFilepath)) {
            throw new \Exception('test-config.yml missing');
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
        /**
         * check for configuration entries; if they are not set, skip test
         */
        if (false === isset($this->config['queryCacheConfig'])) {
            $this->markTestSkipped('Array queryCacheConfig is not set in the config.yml.');
            return;
        } elseif (false === isset($this->config['virtuosoConfig'])) {
            $this->markTestSkipped('Array virtuosoConfig is not set in the config.yml.');
            return;
        }

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

    public function testSetupChainQueryCacheAndVirtuoso()
    {
        /**
         * check for configuration entries; if they are not set, skip test
         */
        if (false === isset($this->config['queryCacheConfig'])) {
            $this->markTestSkipped('Array queryCacheConfig is not set in the config.yml.');
            return;
        } elseif (false === isset($this->config['virtuosoConfig'])) {
            $this->markTestSkipped('Array virtuosoConfig is not set in the config.yml.');
            return;
        }

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
        /**
         * check for configuration entries; if they are not set, skip test
         */
        if (false === isset($this->config['httpConfig'])) {
            $this->markTestSkipped('Array httpConfig is not set in the config.yml.');
            return;
        }

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
        /**
         * check for configuration entries; if they are not set, skip test
         */
        if (false === isset($this->config['virtuosoConfig'])) {
            $this->markTestSkipped('Array virtuosoConfig is not set in the config.yml.');
            return;
        }

        $this->fixture->setupChainEntry($this->config['virtuosoConfig']);

        // load configuration and init Virtuoso instance. no errors expected.
    }
}
