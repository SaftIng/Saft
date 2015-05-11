<?php

namespace Saft\Rdf\Test;

use Saft\Rdf\BlankNodeImpl;
use Saft\Test\TestCase;

abstract class BlankNodeAbstractTest extends TestCase
{
    /**
     * An abstract method which returns new instances of BlankNode
     */
    abstract public function newInstance($blankId);

    final public function testMatches()
    {
        $fixtureA = $this->newInstance('foo');
        $fixtureB = $this->newInstance('foo');
        $fixtureC = $this->newInstance('bar');

        $this->assertTrue($fixtureA->matches($fixtureB));
        $this->assertFalse($fixtureA->matches($fixtureC));
    }
}
