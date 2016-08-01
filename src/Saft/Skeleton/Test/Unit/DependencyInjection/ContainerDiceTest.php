<?php

namespace Saft\Skeleton\Test\Unit\DependencyInjection;

use Saft\Rdf\AnyPatternImpl;
use Saft\Rdf\BlankNodeImpl;
use Saft\Rdf\LiteralImpl;
use Saft\Rdf\NamedNodeImpl;
use Saft\Skeleton\DependencyInjection\ContainerDice;
use Saft\Skeleton\Test\TestCase;

class ContainerDiceTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->fixture = new ContainerDice();
    }

    /*
     * Tests for createInstanceOf
     */

    public function testCreateInstanceOfAnyPattern()
    {
        $this->fixture->setup();

        $this->assertEquals(
            new AnyPatternImpl(),
            $this->fixture->createInstanceOf('Saft\Rdf\AnyPattern', array())
        );
    }

    public function testCreateInstanceOfBlankNode()
    {
        $this->fixture->setup();

        $this->assertEquals(
            new BlankNodeImpl('_:a'),
            $this->fixture->createInstanceOf('Saft\Rdf\BlankNode', array('_:a'))
        );
    }

    public function testCreateInstanceOfLiteral()
    {
        $this->fixture->setup();

        $this->assertEquals(
            new LiteralImpl('foo'),
            $this->fixture->createInstanceOf('Saft\Rdf\Literal', array('foo'))
        );
    }

    public function testCreateInstanceOfNamedNode()
    {
        $this->fixture->setup();

        $this->assertEquals(
            new NamedNodeImpl('http://a'),
            $this->fixture->createInstanceOf('Saft\Rdf\NamedNode', array('http://a'))
        );
    }

    public function testCreateInstanceOfUsageOfSubstitutionsVirtuoso()
    {
        if (false === isset($this->config['virtuosoConfig'])) {
            $this->markTestSkipped('Array virtuosoConfig is not set in the test-config.yml.');
        }

        $this->fixture->setup();

        $virtuoso = $this->fixture->createInstanceOf(
            'Saft\Addition\Virtuoso\Store\Virtuoso',
            array($this->config['virtuosoConfig'])
        );

        $this->assertTrue(is_array($virtuoso->getGraphs()));
    }
}
