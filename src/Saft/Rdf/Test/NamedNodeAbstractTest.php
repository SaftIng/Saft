<?php

namespace Saft\Rdf\Test;

use Saft\Rdf\AnyPatterImpl;
use Saft\Rdf\BlankNodeImpl;
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

    /*
     * Tests instantiation
     */

    public function testInstanciationInvalidUri()
    {
        $this->setExpectedException('\Exception');

        $this->newInstance('foo');
    }

    public function testInstanciationNull()
    {
        // instanciation with null shouldn't be possible and has to lead to an exception
        $this->setExpectedException('\Exception');

        $this->newInstance(null);
    }

    public function testInstanciationValidUri()
    {
        $fixture = $this->newInstance('http://saft/test');
        $this->assertEquals('http://saft/test', $fixture->getUri());
    }

    /*
     * Tests for isBlank
     */

    public function testIsBlank()
    {
        $fixture = $this->newInstance('http://saft/test');
        $this->assertFalse($fixture->isBlank());
    }

    /*
     * Tests for isConcrete
     */

    public function testIsConcrete()
    {
        $fixture = $this->newInstance('http://saft/test');
        $this->assertTrue($fixture->isConcrete());
    }

    /*
     * Tests for isLiteral
     */

    public function testIsLiteral()
    {
        $fixture = $this->newInstance('http://saft/test');
        $this->assertFalse($fixture->isLiteral());
    }

    /*
     * Tests for isNamed
     */

    public function testIsNamed()
    {
        $fixture = $this->newInstance('http://saft/test');
        $this->assertTrue($fixture->isNamed());
    }

    /*
     * Tests for isVariable
     */

    public function testIsVariable()
    {
        $fixture = $this->newInstance('http://saft/test');
        $this->assertFalse($fixture->isVariable());
    }
}
