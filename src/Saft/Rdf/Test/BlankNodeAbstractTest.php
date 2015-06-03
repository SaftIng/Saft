<?php

namespace Saft\Rdf\Test;

use Saft\Rdf\BlankNodeImpl;
use Saft\Rdf\LiteralImpl;
use Saft\Test\TestCase;

abstract class BlankNodeAbstractTest extends TestCase
{
    /**
     * An abstract method which returns new instances of BlankNode
     */
    abstract public function newInstance($blankId);

    /*
     * Tests for equals
     */

    public function testEquals2EqualBlankNodeInstances()
    {
        $instanceA = $this->newInstance('foo');
        $instanceB = $this->newInstance('foo');

        $this->assertTrue($instanceA->equals($instanceB));
    }

    public function testEquals2UnequalBlankNodeInstances()
    {
        $instanceA = $this->newInstance('foo');
        $instanceB = $this->newInstance('bar');

        $this->assertFalse($instanceA->equals($instanceB));
    }

    public function testEqualsCheckBlankNodeAndLiteral()
    {
        $instanceA = $this->newInstance('foo');
        $instanceB = new LiteralImpl('foo');

        $this->assertFalse($instanceA->equals($instanceB));
    }

    /*
     * Tests for isBlank
     */

    public function testIsBlank()
    {
        $this->assertTrue($this->newInstance('foo')->isBlank());
    }

    /*
     * Tests for isConcrete
     */

    public function testIsConcrete()
    {
        $this->assertTrue($this->newInstance('foo')->isConcrete());
    }

    /*
     * Tests for isLiteral
     */

    public function testIsLiteral()
    {
        $this->assertFalse($this->newInstance('foo')->isLiteral());
    }

    /*
     * Tests for isNamed
     */

    public function testIsNamed()
    {
        $this->assertFalse($this->newInstance('foo')->isNamed());
    }

    /*
     * Tests for isVariable
     */

    public function testIsVariable()
    {
        $this->assertFalse($this->newInstance('foo')->isVariable());
    }

    /*
     * Tests for matches
     */

    public function testMatches()
    {
        $fixtureA = $this->newInstance('foo');
        $fixtureB = $this->newInstance('foo');
        $fixtureC = $this->newInstance('bar');

        $this->assertTrue($fixtureA->matches($fixtureB));
        $this->assertFalse($fixtureA->matches($fixtureC));
    }

    /*
     * Tests for toNQuads
     */

    public function testToNQuads()
    {
        $this->assertEquals('_:foo', $this->newInstance('foo')->toNQuads());
    }

    /*
     * Tests for __toString
     */

    public function testToString()
    {
        $this->assertEquals('_:foo', $this->newInstance('foo')->__toString());
    }
}
