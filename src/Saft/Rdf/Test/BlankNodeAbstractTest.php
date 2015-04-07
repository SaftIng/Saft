<?php
namespace Saft\Rdf\Test;

use Saft\Rdf\VariableImpl;
use Saft\Rdf\LiteralImpl;
use Saft\Rdf\BlankNodeImpl;

abstract class BlankNodeAbstractTest extends \PHPUnit_Framework_TestCase
{
    /**
     * An abstract method which returns new instances of BlankNode
     */
    abstract public function newInstance($id);

    /**
     * @expectedException \Exception
     */
    final public function testMatchesChecksPatternType()
    {
        $fixture = $this->newInstance('foo');
        // Will fail. Pattern must be of type BlankNode or Variable
        $fixture->matches(new LiteralImpl('foo'));
    }

    final public function testMatches()
    {
        $fixture = $this->newInstance('foo');

        $this->assertTrue($fixture->matches(new VariableImpl('?s')));
        $this->assertTrue($fixture->matches(new BlankNodeImpl('foo')));
        $this->assertFalse($fixture->matches(new BlankNodeImpl('bar')));
    }
}
