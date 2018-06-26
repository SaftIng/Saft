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

use Saft\Rdf\BlankNode;
use Saft\Rdf\LiteralImpl;

abstract class BlankNodeAbstractTest extends TestCase
{
    /**
     * An abstract method which returns new instances of BlankNode.
     */
    abstract public function getInstance($blankId): BlankNode;

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Blank nodes have to have a string as $blankId.
     */
    public function testConstructorNonStringAsId()
    {
        $this->getInstance(123);
    }

    /*
     * Tests for equals
     */

    public function testEquals2EqualBlankNodeInstances()
    {
        $instanceA = $this->getInstance('foo');
        $instanceB = $this->getInstance('foo');

        $this->assertTrue($instanceA->equals($instanceB));
    }

    public function testEquals2UnequalBlankNodeInstances()
    {
        $instanceA = $this->getInstance('foo');
        $instanceB = $this->getInstance('bar');

        $this->assertFalse($instanceA->equals($instanceB));
    }

    public function testEqualsCheckBlankNodeAndLiteral()
    {
        $instanceA = $this->getInstance('foo');
        $instanceB = new LiteralImpl('foo');

        $this->assertFalse($instanceA->equals($instanceB));
    }

    /*
     * Tests for isBlank
     */

    public function testIsBlank()
    {
        $this->assertTrue($this->getInstance('foo')->isBlank());
    }

    /*
     * Tests for isConcrete
     */

    public function testIsConcrete()
    {
        $this->assertTrue($this->getInstance('foo')->isConcrete());
    }

    /*
     * Tests for isLiteral
     */

    public function testIsLiteral()
    {
        $this->assertFalse($this->getInstance('foo')->isLiteral());
    }

    /*
     * Tests for isNamed
     */

    public function testIsNamed()
    {
        $this->assertFalse($this->getInstance('foo')->isNamed());
    }

    /*
     * Tests for isPattern
     */

    public function testIsPattern()
    {
        $this->assertFalse($this->getInstance('foo')->isPattern());
    }

    /*
     * Tests for matches
     */

    public function testMatches()
    {
        $fixtureA = $this->getInstance('foo');
        $fixtureB = $this->getInstance('foo');
        $fixtureC = $this->getInstance('bar');

        $this->assertTrue($fixtureA->matches($fixtureB));
        $this->assertFalse($fixtureA->matches($fixtureC));
    }

    /*
     * Tests for toNQuads
     */

    public function testToNQuads()
    {
        $this->assertEquals('_:foo', $this->getInstance('foo')->toNQuads());
    }

    /*
     * Tests for __toString
     */

    public function testToString()
    {
        $this->assertEquals('_:foo', $this->getInstance('foo')->__toString());
    }
}
