<?php

namespace Saft\Test;

use Saft\Rdf\NamedNodeImpl;
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
     * @var NamedNode
     */
    protected $testGraph;

    /**
     * http://stackoverflow.com/a/12496979
     * Fixes assertEquals in case of check array equality.
     *
     * @param array  $expected
     * @param array  $actual
     * @param string $message  optional
     */
    protected function assertEqualsArrays($expected, $actual, $message = '')
    {
        sort($expected);
        sort($actual);

        $this->assertEquals($expected, $actual, $message);
    }

    /**
     * compares two SPARQL query strings by removing all whitespace. This method still does not ensur semantic equality
     * and will also lose information about neccessary whitespace.
     */
    public function assertEqualsSparql($expected, $actual, $message = '')
    {
        $expected = preg_replace('/\s+/', '', $expected);
        $actual = preg_replace('/\s+/', '', $actual);
        $this->assertEquals($expected, $actual, $message);
    }

    /**
     * This assertion consumes the StatementIterator and counts its entries until it is empty. It automatically
     * calls assertTrue and -False on $statementIterator->valid() from time to time.
     *
     * @param int               $expectedCount
     * @param StatementIterator $statementIterator
     * @param string            $message
     */
    public function assertCountStatementIterator($expectedCount, $statementIterator, $message = null)
    {
        if (true == empty($message)) {
            $message = 'Assertion about count of statements. Expected: '. $expectedCount .', Actual: %s';
        }

        for ($i = 0; $i < $expectedCount; ++$i) {
            $this->assertTrue(
                $statementIterator->valid(),
                sprintf($message, $i)
            );
            $statementIterator->next();
        }
        $statementIterator->next();
        $this->assertFalse($statementIterator->valid(), sprintf($message, 'at least '. $i + 1));
    }

    /**
     * Loads configuration file test-config.yml. If the file does not exists, the according test will be
     * marked as skipped with a short notification about that the file is missing.
     *
     * The content of the YAML-file will be transformed into an array and stored in $config property.
     */
    protected function loadTestConfiguration()
    {
        // set path to test dir
        $saftRootDir = dirname(__FILE__) . '/../../../';
        $configFilepath = $saftRootDir . 'test-config.yml';

        // check that the config file exists
        if (false === file_exists($configFilepath)) {
            $this->markTestSkipped('File test-config.yml is missing.');
        }

        // parse YAML file
        $yaml = new Parser();
        $this->config = $yaml->parse(file_get_contents($configFilepath));
    }

    /**
     * Place to setup stuff for Saft related tests.
     */
    public function setUp()
    {
        parent::setUp();

        $this->testGraph = new NamedNodeImpl('http://localhost/Saft/TestGraph/');

        $this->loadTestConfiguration();
    }
}
