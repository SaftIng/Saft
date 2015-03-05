<?php
namespace Saft\Store;

use Symfony\Component\Yaml\Parser;

abstract class TestCase extends \PHPUnit_Framework_TestCase
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
     * Contains an instance of the class to test.
     *
     * @var mixed
     */
    protected $fixture;

    /**
     * @var string
     */
    protected $testGraphUri = "http://localhost/Saft/TestGraph/";

    /**
     * http://stackoverflow.com/a/12496979
     * Fixes assertEquals in case of check array equality.
     *
     * @param array  $expected
     * @param array  $actual
     * @param string $message  optional
     */
    protected function assertEqualsArrays($expected, $actual, $message = "")
    {
        sort($expected);
        sort($actual);

        $this->assertEquals($expected, $actual, $message);
    }

    /**
     *
     */
    public function setUp()
    {
        parent::setUp();

        // set path to root dir, usually where to saft-skeleton
        // TODO move config.yml stuff to Saft.store package
        $saftRootDir = dirname(__FILE__) . '/../../../';
        $configFilepath = $saftRootDir . 'test/config.yml';

        // check for config file
        if (false === file_exists($configFilepath)) {
            throw new \Exception('config.yml missing in test/config.yml');
        }

        // parse YAML file
        $yaml = new Parser();
        $this->config = $yaml->parse(file_get_contents($configFilepath));
    }
}
