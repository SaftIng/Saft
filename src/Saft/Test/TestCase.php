<?php

/*
 * This file is part of Saft.
 *
 * (c) Konrad Abicht <hi@inspirito.de>
 * (c) Natanael Arndt <arndt@informatik.uni-leipzig.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Saft\Test;

use Saft\Rdf\CommonNamespaces;
use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\RdfHelpers;
use Saft\Rdf\Statement;
use Saft\Rdf\StatementFactoryImpl;
use Saft\Rdf\StatementIterator;
use Saft\Rdf\StatementIteratorFactoryImpl;
use Saft\Sparql\Result\Result;
use Saft\Sparql\Result\SetResult;
use Symfony\Component\Yaml\Parser;

/**
 * @api
 * @since 0.1
 */
abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    protected $configuration;

    protected $commonNamespaces;

    /**
     * Contains an instance of the class to test.
     *
     * @var mixed
     */
    protected $fixture;

    protected $nodeFactory;
    protected $rdfHelpers;
    protected $statementFactory;
    protected $statementIteratorFactory;

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
     * @api
     * @since 0.1
     */
    protected function assertEqualsArrays($expected, $actual, $message = '')
    {
        sort($expected);
        sort($actual);

        $this->assertEquals($expected, $actual, $message);
    }

    /**
     * It checks of the given instance implements a certain class or interface.
     *
     * @param object $instance         Instance to check.
     * @param string $classOrInterface Name of the class or interface to check if it is implemented by $instance.
     * @api
     * @since 0.1
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
     * @api
     * @since 0.1
     */
    public function assertCountStatementIterator($expectedCount, $statementIterator, $message = null)
    {
        if (true == empty($message)) {
            $message = 'Assertion about count of statements. Expected: '. $expectedCount .', Actual: %s';
        }

        $i = 0;
        foreach ($statementIterator as $statement) {
            ++$i;
        }
        $this->assertEquals($i, $expectedCount, sprintf($message, $i));
    }

    /**
     * Checks two lists which implements \Iterator interface, if they contain the same Statement instances.
     * The checks will be executed using PHPUnit's assert functions.
     *
     * @param SetResult $expected
     * @param SetResult $actual
     * @api
     * @since 0.1
     */
    public function assertSetIteratorEquals(SetResult $expected, SetResult $actual)
    {
        $expectedEntries = array();
        foreach ($expected as $entry) {
            // serialize entry and hash it afterwards to use it as key for $entriesToCheck array.
            // later on we only check the other list that each entry, serialized and hashed, has
            // its equal key in the list.
            // the structure of each entry is an associative array which contains Node instances.
            $entryString = '';
            foreach ($entry as $key => $nodeInstance) {
                if ($nodeInstance->isConcrete()) {
                    // build a string of all entries of $entry and generate a hash based on that later on.
                    $entryString .= $nodeInstance->toNQuads() . ' ';
                } else {
                    throw new \Exception('Non-concrete Node instance in SetResult instance found.');
                }
            }
            $expectedEntries[$entryString] = $entry;
        }

        $actualEntries = array();
        foreach ($actual as $entry) {
            $entryString = '';
            foreach ($entry as $key => $nodeInstance) {
                if ($nodeInstance->isConcrete()) {
                    // build a string of all entries of $entry and generate a hash based on that later on.
                    $entryString .= $nodeInstance->toNQuads() . ' ';
                } else {
                    throw new \Exception('Non-concrete Node instance in SetResult instance found.');
                }
            }
            $actualEntries[$entryString] = $entry;
        }

        $notFoundEntries = array();
        foreach ($expectedEntries as $expectedEntry) {
            $foundExpectedEntry = false;

            // 1. generate a string which represents all nodes of an expected set entry
            $expectedEntryString = '';
            foreach ($expectedEntry as $nodeInstance) {
                $expectedEntryString .= $nodeInstance->toNQuads() . ' ';
            }

            // 2. for each actual entry check their generated string against the expected one
            foreach ($actualEntries as $actualEntry) {
                $actualEntryString = '';
                foreach ($actualEntry as $nodeInstance) {
                    $actualEntryString .= $nodeInstance->toNQuads() . ' ';
                }
                if ($actualEntryString == $expectedEntryString) {
                    $foundExpectedEntry = true;
                    break;
                }
            }

            if (false == $foundExpectedEntry) {
                $notFoundEntries[] = $expectedEntryString;
            }
        }

        // first simply check of the number of given actual entries and expected
        if (count($actualEntries) != count($expectedEntries)) {
            $this->fail('Expected '. count($expectedEntries) . ' entries, but got '. count($actualEntries));
        }

        if (!empty($notFoundEntries)) {
            echo PHP_EOL . PHP_EOL . 'Given entries, but not found:' . PHP_EOL;
            var_dump($notFoundEntries);

            echo PHP_EOL . PHP_EOL . 'Actual entries:' . PHP_EOL;
            foreach ($actualEntries as $entries) {
                echo '- ';
                foreach ($entries as $entry) {
                    echo $entry->toNQuads() .' ';
                }
                echo PHP_EOL;
                echo PHP_EOL;
            }

            $this->fail(count($notFoundEntries) .' entries where not found.');

        // check variables in the end
        } elseif (0 == count($notFoundEntries)) {
            $this->assertEquals($expected->getVariables(), $actual->getVariables());
        }
    }

    /**
     * Checks two lists which implements \Iterator interface, if they contain the same elements.
     * The checks will be executed using PHPUnit's assert functions.
     *
     * @param StatementIterator $expected
     * @param StatementIterator $actual
     * @param boolean $debug optional, default: false
     * @todo implement a more precise way to check blank nodes (currently we just count expected
     *       and actual numbers of statements with blank nodes)
     * @api
     * @since 0.1
     */
    public function assertStatementIteratorEquals(
        StatementIterator $expected,
        StatementIterator $actual,
        $debug = false
    ) {
        $entriesToCheck = array();
        $expectedStatementsWithBlankNodeCount = 0;

        foreach ($expected as $statement) {
            // serialize entry and hash it afterwards to use it as key for $entriesToCheck array.
            // later on we only check the other list that each entry, serialized and hashed, has
            // its equal key in the list.
            if (!$statement->isConcrete()) {
                $this->markTestIncomplete('Comparison of variable statements in iterators not yet implemented.');
            }
            if ($this->statementContainsNoBlankNodes($statement)) {
                $entriesToCheck[hash('sha256', $statement->toNQuads())] = false;
            } else {
                ++$expectedStatementsWithBlankNodeCount;
            }
        }

        // contains a list of all entries, which were not found in $expected.
        $actualEntriesNotFound = array();
        $notCheckedEntries = array();
        $foundEntries = array();
        $actualStatementsWithBlankNodeCount = 0;

        foreach ($actual as $statement) {
            if (!$statement->isConcrete()) {
                $this->markTestIncomplete("Comparison of variable statements in iterators not yet implemented.");
            }
            $statmentHash = hash('sha256', $statement->toNQuads());
            // statements without blank nodes
            if (isset($entriesToCheck[$statmentHash]) && $this->statementContainsNoBlankNodes($statement)) {
                // if entry was found, mark it.
                $entriesToCheck[$statmentHash] = true;
                $foundEntries[] = $statement;

            // handle statements with blank nodes separate because blanknode ID is random
            // and therefore gets lost when stored (usually)
            } elseif (false == $this->statementContainsNoBlankNodes($statement)) {
                ++$actualStatementsWithBlankNodeCount;

            // statement was not found
            } else {
                $actualEntriesNotFound[] = $statement;
                $notCheckedEntries[] = $statement;
            }
        }

        if (!empty($actualEntriesNotFound) || !empty($notCheckedEntries)) {
            $message = 'The StatementIterators are not equal.';
            if (!empty($actualEntriesNotFound)) {
                if ($debug) {
                    echo PHP_EOL . 'Following statements where not expected, but found: ';
                    var_dump($actualEntriesNotFound);
                }
                $message .= ' ' . count($actualEntriesNotFound) . ' Statements where not expected.';
            }
            if (!empty($notCheckedEntries)) {
                if ($debug) {
                    echo PHP_EOL . 'Following statements where not present, but expected: ';
                    var_dump($notCheckedEntries);
                }
                $message .= ' ' . count($notCheckedEntries) . ' Statements where not present but expected.';
            }
            $this->assertFalse(!empty($actualEntriesNotFound) || !empty($notCheckedEntries), $message);

        // compare count of statements with blank nodes
        } elseif ($expectedStatementsWithBlankNodeCount != $actualStatementsWithBlankNodeCount) {
            $this->assertFalse(
                true,
                'Some statements with blank nodes where not found. '
                    . 'Expected: ' . $expectedStatementsWithBlankNodeCount
                    . 'Actual: ' . $actualStatementsWithBlankNodeCount
            );

        } else {
            $this->assertTrue(true);
        }
    }

    /**
     * Checks two SPARQL Results whether they are equal.
     * The checks will be executed using PHPUnit's assert functions.
     *
     * @param Result $expected
     * @param Result $actual
     * @throws \Exception if unknown Result type was given.
     * @api
     * @since 0.1
     */
    public function assertResultEquals(Result $expected, Result $actual)
    {
        $this->assertEquals($expected->isEmptyResult(), $actual->isEmptyResult());
        $this->assertEquals($expected->isSetResult(), $actual->isSetResult());
        $this->assertEquals($expected->isStatementSetResult(), $actual->isStatementSetResult());
        $this->assertEquals($expected->isValueResult(), $actual->isValueResult());

        // general result
        if ($expected->isSetResult()) {
            $this->assertSetIteratorEquals($expected, $actual);

        // statement result
        } elseif ($actual->isStatementSetResult() && $expected->isStatementSetResult()) {
            $this->assertStatementIteratorEquals($expected, $actual);

        // value result
        } elseif ($expected->isValueResult()) {
            $this->assertEquals($expected->getValue(), $actual->getValue());

        } else {
            throw new \Exception('Unknown Result type given.');
        }
    }

    /**
     * Loads configuration file test-config.yml. If the file does not exists, the according test will be
     * marked as skipped with a short notification about that the file is missing.
     *
     * The content of the YAML-file will be transformed into an array and stored in $config property.
     *
     * @param string $configFilePath Path to the config file.
     * @api
     * @since 0.1
     */
    protected function loadTestConfiguration($configFilepath)
    {
        // check that the config file exists
        if (false === file_exists($configFilepath)) {
            $this->markTestSkipped('File test-config.yml is missing.');
        }

        // parse YAML file
        $yaml = new Parser();
        $this->configuration = $yaml->parse(file_get_contents($configFilepath));
    }

    /**
     * Place to setup stuff for Saft related tests.
     *
     * @api
     * @since 0.1
     */
    public function setUp()
    {
        parent::setUp();

        $this->commonNamespaces = new CommonNamespaces();
        $this->rdfHelpers = new RdfHelpers();
        $this->nodeFactory = new NodeFactoryImpl($this->rdfHelpers);
        $this->statementFactory = new StatementFactoryImpl($this->rdfHelpers);
        $this->statementIteratorFactory = new StatementIteratorFactoryImpl();
        $this->testGraph = $this->nodeFactory->createNamedNode('http://localhost/Saft/TestGraph/');
    }

    /**
     * @param Statement $statement
     * @return bool
     */
    protected function statementContainsNoBlankNodes(Statement $statement)
    {
        return false == $statement->getSubject()->isBlank()
            && false == $statement->getObject()->isBlank();
    }
}
