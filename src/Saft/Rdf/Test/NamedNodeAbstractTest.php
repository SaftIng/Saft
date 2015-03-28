<?php
namespace Saft\Rdf\Test;


abstract class NamedNodeAbstractTest extends \PHPUnit_Framework_TestCase
{

    /**
     * An abstract method which returns new instances of NamedNode
     * @todo The factory method approach could also be extended to use a factory object
     */
    abstract public function newInstance($uri);


    /**
     * Tests check
     * What is this check for? There shouldn't be a method check
     */
    public function testCheck()
    {
        $fixture = $this->newInstance('http://saft/test');
        $this->assertFalse($fixture->check(''));
        $this->assertFalse($fixture->check('http//foobar/'));

        $this->assertTrue($fixture->check('http:foobar/'));
        $this->assertTrue($fixture->check('http://foobar/'));
        $this->assertTrue($fixture->check('http://foobar:42/'));
        $this->assertTrue($fixture->check('http://foo:bar@foobar/'));
    }

    /**
     * Tests instanciation
     */
    public function testInstanciationInvalidUri()
    {
        $this->setExpectedException('\Exception');

        $this->newInstance('foo');
    }

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
}