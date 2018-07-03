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

namespace Saft\Addition\hardf\Test\Data;

use Saft\Addition\hardf\Data\ParserHardf;
use Saft\Addition\hardf\Rdf\LazyStatementIterator;
use Saft\Data\Parser;
use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\RdfHelpers;
use Saft\Rdf\StatementFactoryImpl;
use Saft\Rdf\StatementIterator;
use Saft\Rdf\StatementIteratorFactoryImpl;
use Saft\Rdf\Test\TestCase;

class ParserHardfTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->fixture = $this->getInstance('turtle');
    }

    protected function getInstance(string $serialization): Parser
    {
        return new ParserHardf(
            $this->nodeFactory,
            $this->statementFactory,
            $this->statementIteratorFactory,
            $this->rdfHelpers,
            $serialization
        );
    }

    /*
     * Tests for __construct
     */

    /**
     * @expectedException \Exception
     */
    public function testConstructorInvalidSerialization()
    {
        $this->getInstance('unknown');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Currently not implemented.
     */
    public function testGetCurrentPrefixList()
    {
        $this->getInstance('n-triples')->getCurrentPrefixList();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage $baseUri is not a valid URI.
     */
    public function testParseStringToIteratorInvalidBaseUri()
    {
        $this->getInstance('n-triples')->parseStringToIterator('foo',
            'invalid'
        );
    }

    // test a special case in a foreign application which uses this functionality
    public function testParseStringToIteratorForTurtle()
    {
        $iterator = $this->getInstance('turtle')->parseStringToIterator('
            @prefix foo: <http://foo/> .
            foo:bar1 a foo:bar2 ;
                foo:bar2 "baz" ;
                foo:bar3 [
                    foo:bar4 "foobar"
                ] ;
                foo:bar3 [
                    foo:event foo:foobar2 ;
                    foo:foobaz "true"
                ] .

            foo:Event foo:baz foo:baz2 .
            foo:foobar2 foo:baz2 foo:baz3 .',
            $this->testGraph
        );

        $statementIteratorFactory = new StatementIteratorFactoryImpl();
        $expected = $statementIteratorFactory->createStatementIteratorFromArray([
            $this->statementFactory->createStatement(
                $this->nodeFactory->createNamedNode('http://foo/bar1'),
                $this->nodeFactory->createNamedNode('http://www.w3.org/1999/02/22-rdf-syntax-ns#type'),
                $this->nodeFactory->createNamedNode('http://foo/bar2')
            ),
            $this->statementFactory->createStatement(
                $this->nodeFactory->createNamedNode('http://foo/bar1'),
                $this->nodeFactory->createNamedNode('http://foo/bar2'),
                $this->nodeFactory->createLiteral('baz')
            ),
            $this->statementFactory->createStatement(
                $this->nodeFactory->createBlankNode('b0'),
                $this->nodeFactory->createNamedNode('http://foo/bar4'),
                $this->nodeFactory->createLiteral('foobar')
            ),
            $this->statementFactory->createStatement(
                $this->nodeFactory->createNamedNode('http://foo/bar1'),
                $this->nodeFactory->createNamedNode('http://foo/bar3'),
                $this->nodeFactory->createBlankNode('b0')
            ),
            $this->statementFactory->createStatement(
                $this->nodeFactory->createBlankNode('b1'),
                $this->nodeFactory->createNamedNode('http://foo/event'),
                $this->nodeFactory->createNamedNode('http://foo/foobar2')
            ),
            $this->statementFactory->createStatement(
                $this->nodeFactory->createBlankNode('b1'),
                $this->nodeFactory->createNamedNode('http://foo/foobaz'),
                $this->nodeFactory->createLiteral('true')
            ),
            $this->statementFactory->createStatement(
                $this->nodeFactory->createNamedNode('http://foo/bar1'),
                $this->nodeFactory->createNamedNode('http://foo/bar3'),
                $this->nodeFactory->createBlankNode('b1')
            ),
            $this->statementFactory->createStatement(
                $this->nodeFactory->createNamedNode('http://foo/Event'),
                $this->nodeFactory->createNamedNode('http://foo/baz'),
                $this->nodeFactory->createNamedNode('http://foo/baz2')
            ),
            $this->statementFactory->createStatement(
                $this->nodeFactory->createNamedNode('http://foo/foobar2'),
                $this->nodeFactory->createNamedNode('http://foo/baz2'),
                $this->nodeFactory->createNamedNode('http://foo/baz3')
            ),
        ]);

        $this->assertEquals($expected, $iterator);
    }

    // test that guessFormat doesn't confuse n-triples with turtle
    public function testParseStringToIteratorRegression1()
    {
        $fileContent = file_get_contents(__DIR__.'/../resources/guessFormat-regression1.ttl');

        $iterator = $this->getInstance('turtle')->parseStringToIterator($fileContent);

        $counter = 0;
        foreach ($iterator as $key => $value) {
            ++$counter;
        }

        $this->assertEquals(149, $counter);
    }

    /*
     * Tests for parseStreamToIterator
     */

    public function testParseStreamToIteratorLazyStatementIterator()
    {
        $iterator = $this->getInstance('n-triples')->parseStreamToIterator(__DIR__.'/../resources/n-triples-example.nt');

        $this->assertTrue($iterator instanceof LazyStatementIterator);

        $counter = 0;
        foreach ($iterator as $value) {
            ++$counter;
        }

        $this->assertEquals(5, $counter);
    }

    public function testParseStreamToIteratorArrayBasedStatementIterator()
    {
        $iterator = $this->getInstance('turtle')->parseStreamToIterator(__DIR__.'/../resources/guessFormat-regression1.ttl');

        $this->assertTrue($iterator instanceof StatementIterator);

        $counter = 0;
        foreach ($iterator as $value) {
            ++$counter;
        }

        $this->assertEquals(149, $counter);
    }
}
