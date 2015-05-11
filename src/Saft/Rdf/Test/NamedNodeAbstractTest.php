<?php

namespace Saft\Rdf\Test;

use Saft\Rdf\BlankNodeImpl;
use Saft\Rdf\AnyPatterImpl;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\LiteralImpl;
use Saft\Test\TestCase;

abstract class NamedNodeAbstractTest extends TestCase
{
    /**
     * An abstract method which returns new instances of NamedNode
     * @todo The factory method approach could also be extended to use a factory object
     */
    abstract public function newInstance($uri);

    public function testEquals()
    {
        $fixtureA = $this->newInstance('http://saft/test');
        $fixtureB = $this->newInstance('http://saft/test');
        $fixtureC = $this->newInstance('http://saft/testOther');

        // TODO compare with literal, pattern, blanknode

        $this->assertTrue($fixtureA->equals($fixtureA));
        $this->assertTrue($fixtureA->equals($fixtureB));
        $this->assertFalse($fixtureA->equals($fixtureC));
    }


    /**
     * Tests instanciation
     */
    public function testInstanciationInvalidUri()
    {
        $this->setExpectedException('\Exception');

        $this->newInstance('foo');
    }

    /**
     * instanciation with null shouldn't be possible
     */
    public function testInstanciationNull()
    {
        $this->setExpectedException('\Exception');

        $this->newInstance(null);
    }

    public function testInstanciationValidUri()
    {
        $fixture = $this->newInstance('http://saft/test');
        $this->assertEquals('http://saft/test', $fixture->getUri());
    }

    /**
     * Tests isBlank
     */
    public function testIsBlank()
    {
        $fixture = $this->newInstance('http://saft/test');
        $this->assertFalse($fixture->isBlank());
    }

    /**
     * Tests isConcrete
     */
    public function testIsConcrete()
    {
        $fixture = $this->newInstance('http://saft/test');
        $this->assertTrue($fixture->isConcrete());
    }

    /**
     * Tests isLiteral
     */
    public function testIsLiteral()
    {
        $fixture = $this->newInstance('http://saft/test');
        $this->assertFalse($fixture->isLiteral());
    }

    /**
     * Tests isNamed
     */
    public function testIsNamed()
    {
        $fixture = $this->newInstance('http://saft/test');
        $this->assertTrue($fixture->isNamed());
    }

    /**
     * Tests isVariable
     */
    public function testIsVariable()
    {
        $fixture = $this->newInstance('http://saft/test');
        $this->assertFalse($fixture->isVariable());
    }
}
