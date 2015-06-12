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
     * It checks of the given instance implements a certain class or interface.
     *
     * @param object $instance         Instance to check.
     * @param string $classOrInterface Name of the class or interface to check if it is implemented
     *                                 by $instance.
     */
    public function assertClassOfInstanceImplements($instance, $classOrInterface)
    {
        $this->assertTrue(is_object($instance), '$instance is not an object.');

        $implements = class_implements($instance);
        $this->assertTrue(isset($implements[$classOrInterface]));
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
     * Checks two lists which implements \Iterator interface, if they contain the same elements.
     * The checks will be executed using PHPUnit's assert functions.
     *
     * @param Iterator $expected
     * @param Iterator $actual
     */
    public function assertIteratorContent($expected, $actual)
    {
        $entriesToCheck = array();
        $expectedCount = 0;
        $notCheckedEntries = array();

        // contains a list of all entries, which were not found in $expected.
        $actualEntriesNotFound = array();
        $actualCount = 0;

        foreach ($expected as $entry) {
            // serialize entry and hash it afterwards to use it as key for $entriesToCheck array.
            // later on we only check the other list that each entry, serialized and hashed, has
            // its equal key in the list.
            $hashedEntry = hash('sha256', serialize($entry));
            $entriesToCheck[$hashedEntry] = 'not checked';

            ++$expectedCount;
        }

        foreach ($actual as $entry) {
            $hashedEntry = hash('sha256', serialize($entry));

            // if entry was found, mark it.
            if (isset($entriesToCheck[$hashedEntry])) {
                $entriesToCheck[$hashedEntry] = 'checked';

            // entry was not found
            } else {
                $actualEntriesNotFound[] = $entry;
            }

            ++$actualCount;
        }

        // check that both lists contain the same amount of elements
        $this->assertEquals(
            $expectedCount,
            $actualCount,
            'Number of entries of both instances differ. Expected: '. $expectedCount .', Actual: '. $actualCount
        );

        // check that all entries from $expected were checked
        foreach ($entriesToCheck as $entry) {
            if ('not checked' === $entry) {
                $notCheckedEntries[] = $entry;
            }
        }

        $this->assertEquals(
            array(),
            $notCheckedEntries,
            'The following entries are not part of $actual-iterator.'
        );
    }

    /**
     * Loads configuration file test-config.yml. If the file does not exists, the according test will be
     * marked as skipped with a short notification about that the file is missing.
     *
     * The content of the YAML-file will be transformed into an array and stored in $config property.
     *
     * @param string $configFilePath Path to the config file.
     */
    protected function loadTestConfiguration($configFilepath)
    {
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

        // set path to test file
        $saftRootDir = dirname(__FILE__) . '/../../../';
        $configFilepath = $saftRootDir . 'test-config.yml';

        $this->loadTestConfiguration($configFilepath);
    }
}
