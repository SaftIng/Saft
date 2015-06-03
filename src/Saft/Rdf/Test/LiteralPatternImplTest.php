<?php

namespace Saft\Rdf\Test;

use Saft\Rdf\AnyPatternImpl;
use Saft\Rdf\BlankNodeImpl;
use Saft\Rdf\LiteralPatternImpl;
use Saft\Rdf\LiteralImpl;
use Saft\Test\TestCase;

class LiteralPatternImplTest extends TestCase
{
    /*
     * Tests for equals
     */

    public function testEquals2EqualInstances()
    {
        $instanceA = new LiteralPatternImpl('foo', $this->testGraph);
        $instanceB = new LiteralPatternImpl('foo', $this->testGraph);

        $this->assertTrue($instanceA->equals($instanceB));
    }

    public function testEquals2UnequalInstances()
    {
        $instanceA = new LiteralPatternImpl('foo', $this->testGraph);
        $instanceB = new LiteralPatternImpl('bar', $this->testGraph);

        $this->assertFalse($instanceA->equals($instanceB));
    }

    public function testEqualsCheckLiteralPatternAndLiteral()
    {
        $instanceA = new LiteralPatternImpl('foo', $this->testGraph);
        $instanceB = new LiteralImpl('foo');

        $this->assertFalse($instanceA->equals($instanceB));
    }

    /*
     * Tests for isBlank
     */

    public function testIsBlank()
    {
        $fixture = new LiteralPatternImpl('foo', $this->testGraph);
        $this->assertFalse($fixture->isBlank());
    }

    /*
     * Tests for isConcrete
     */

    public function testIsConcrete()
    {
        $fixture = new LiteralPatternImpl('foo', $this->testGraph);
        $this->assertFalse($fixture->isConcrete());
    }

    /*
     * Tests for isLiteral
     */

    public function testIsLiteral()
    {
        $fixture = new LiteralPatternImpl('foo', $this->testGraph);
        $this->assertFalse($fixture->isLiteral());
    }

    /*
     * Tests for isNamed
     */

    public function testIsNamed()
    {
        $fixture = new LiteralPatternImpl('foo', $this->testGraph);
        $this->assertFalse($fixture->isNamed());
    }

    /*
     * Tests for isVariable
     */

    public function testIsVariable()
    {
        $fixture = new LiteralPatternImpl('foo', $this->testGraph);
        $this->assertTrue($fixture->isVariable());
    }

    /*
     * Tests for matches
     */

    public function testMatches()
    {
        $instanceA = new LiteralPatternImpl('foo', $this->testGraph);
        $instanceB = new LiteralImpl('foo', $this->testGraph);

        $this->assertTrue($instanceA->matches($instanceB));
    }

    public function testMatchesCheckPartialMatchesAreNoMatches()
    {
        $instanceA = new LiteralPatternImpl('foo', $this->testGraph);
        $instanceB = new LiteralImpl('foo');

        $this->assertFalse($instanceA->matches($instanceB));
    }

    public function testMatchesCheckDifferentTypes()
    {
        $instanceA = new LiteralPatternImpl('foo', $this->testGraph);
        $instanceB = new BlankNodeImpl('foo');

        $this->assertFalse($instanceA->matches($instanceB));
    }

    public function testMatchesCheckNodeToBeConcrete()
    {
        $instanceA = new LiteralPatternImpl('foo', $this->testGraph);
        $instanceB = new AnyPatternImpl();

        $this->setExpectedException('\Exception');
        $instanceA->matches($instanceB);
    }

    /*
     * Tests for toNQuads
     */

    public function testToNQuads()
    {
        $fixture = new LiteralPatternImpl('foo', $this->testGraph);

        // expect exception because LiteralPattern is not valid in NQuads
        $this->setExpectedException('\Exception');
        $fixture->toNQuads();
    }
}
