<?php
namespace Saft\Rdf\Test;

use Saft\Rdf\BlankNodeImpl;
use Saft\Rdf\VariableImpl;
use Saft\Rdf\NamedNodeImpl;

abstract class NamedNodeAbstractTest extends \PHPUnit_Framework_TestCase
{
    /**
     * An abstract method which returns new instances of NamedNode
     * @todo The factory method approach could also be extended to use a factory object
     */
    abstract public function newInstance($uri);

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
        $fixture = $this->newInstance(null);
        $this->assertEquals(null, $fixture->getValue());
    }

    public function testInstanciationValidUri()
    {
        $fixture = $this->newInstance('http://saft/test');
        $this->assertEquals('http://saft/test', $fixture->getValue());
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

    /**
     * @expectedException \Exception
     */
    final public function testMatchesChecksPatternType()
    {
        $fixture = $this->newInstance('http://example.net');
        // Will fail. Pattern must be of type NamedNode or Variable
        $fixture->matches(new BlankNodeImpl('foo'));
    }

    final public function testMatches()
    {
        $fixture = $this->newInstance('http://example.net');

        $this->assertTrue($fixture->matches(new VariableImpl('?s')));
        $this->assertTrue($fixture->matches(new NamedNodeImpl('http://example.net')));
        $this->assertFalse($fixture->matches(new NamedNodeImpl('http://other.net')));
    }
}
