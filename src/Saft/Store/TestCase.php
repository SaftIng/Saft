<?php
namespace Saft\Store;

use Symfony\Component\Yaml\Parser;

class TestCase extends \Saft\TestCase
{
    /**
     * @var Saft\Cache
     */
    protected $cache;

    /**
     * @var array
     */
    protected $config;

    /**
     *
     */
    public function setUp()
    {
        parent::setUp();

        // set path to root dir, usually where to saft-skeleton
        // TODO move config.yml stuff to Saft.store package
        $saftRootDir = dirname(__FILE__) . '/../../../../../../';
        $configFilepath = $saftRootDir . 'test/config.yml';

        // check for config file
        if (false === file_exists($configFilepath)) {
            throw new \Exception('config.yml missing in test/config.yml');
        }

        // parse YAML file
        $yaml = new Parser();
        $this->config = $yaml->parse(file_get_contents($configFilepath));

        // setup cache
        $this->cache = new \Saft\Cache($this->config['configuration']['standardCache']);
        $this->cache->clean();
    }
}
