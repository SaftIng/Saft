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

namespace Saft\Rdf\Test;

use Saft\Rdf\NamedNode;

abstract class NamedNodeAbstractTest extends TestCase
{
    /**
     * An abstract method which returns new instances of NamedNode.
     *
     * @todo The factory method approach could also be extended to use a factory object
     */
    abstract public function getInstance($uri): NamedNode;

    public function testEquals()
    {
        $fixtureA = $this->getInstance('http://saft/test');
        $fixtureB = $this->getInstance('http://saft/test');
        $fixtureC = $this->getInstance('http://saft/testOther');

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
        $this->expectException('\Exception');

        $this->getInstance('foo');
    }

    public function testInstanciationNull()
    {
        // instanciation with null shouldn't be possible and has to lead to an exception
        $this->expectException('\Exception');

        $this->getInstance(null);
    }

    public function testInstanciationValidUri()
    {
        $fixture = $this->getInstance('http://saft/test');
        $this->assertEquals('http://saft/test', $fixture->getUri());
    }

    /*
     * Tests for isBlank
     */

    public function testIsBlank()
    {
        $fixture = $this->getInstance('http://saft/test');
        $this->assertFalse($fixture->isBlank());
    }

    /*
     * Tests for isConcrete
     */

    public function testIsConcrete()
    {
        $fixture = $this->getInstance('http://saft/test');
        $this->assertTrue($fixture->isConcrete());
    }

    /*
     * Tests for isLiteral
     */

    public function testIsLiteral()
    {
        $fixture = $this->getInstance('http://saft/test');
        $this->assertFalse($fixture->isLiteral());
    }

    /*
     * Tests for isNamed
     */

    public function testIsNamed()
    {
        $fixture = $this->getInstance('http://saft/test');
        $this->assertTrue($fixture->isNamed());
    }

    /*
     * Tests for isPattern
     */

    public function testIsPattern()
    {
        $fixture = $this->getInstance('http://saft/test');
        $this->assertFalse($fixture->isPattern());
    }
}
