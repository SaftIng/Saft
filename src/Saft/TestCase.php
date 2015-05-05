<?php

namespace Saft;

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
}
