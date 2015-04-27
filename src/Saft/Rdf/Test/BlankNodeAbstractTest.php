<?php
namespace Saft\Rdf\Test;

use Saft\Rdf\BlankNodeImpl;

abstract class BlankNodeAbstractTest extends \PHPUnit_Framework_TestCase
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
