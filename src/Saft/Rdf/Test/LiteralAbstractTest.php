<?php
namespace Saft\Rdf\Test;

use Saft\Rdf\VariableImpl;
use Saft\Rdf\LiteralImpl;
use Saft\Rdf\BlankNodeImpl;

abstract class LiteralAbstractTest extends \PHPUnit_Framework_TestCase
{

    /**
     * An abstract method which returns new instances of Literal
     * @todo The factory method approach could also be extended to use a factory object
     */
    abstract public function newInstance($value, $lang = null, $datatype = null);

    /**
     * Tests equals
     */
    public function testEquals()
    {
        $fixture = $this->newInstance(true);
        $toCompare = $this->newInstance(true);

        $this->assertTrue($fixture->equals($toCompare));
    }

    public function testEqualsDifferentType()
    {
        $fixture = $this->newInstance(1);
        $toCompare = $this->newInstance(1.0);

        $this->assertFalse($fixture->equals($toCompare));
    }

    /**
     * Tests getDatatype
     */
    public function testGetDatatypeBoolean()
    {
        $fixture = $this->newInstance(true);

        $this->assertEquals(
            'http://www.w3.org/2001/XMLSchema#boolean',
            $fixture->getDatatype()
        );
    }

    public function testGetDatatypeLangSet()
    {
        $fixture = $this->newInstance('foo', 'en');

        $this->assertNull($fixture->getDatatype());
    }

    public function testGetDatatypeDecimal()
    {
        $fixture = $this->newInstance(3.18);

        $this->assertEquals(
            'http://www.w3.org/2001/XMLSchema#decimal',
            $fixture->getDatatype()
        );
    }

    public function testGetDatatypeInteger()
    {
        $fixture = $this->newInstance(3);

        $this->assertEquals(
            'http://www.w3.org/2001/XMLSchema#integer',
            $fixture->getDatatype()
        );
    }

    public function testGetDatatypeString()
    {
        $fixture = $this->newInstance('foo');

        $this->assertEquals(
            'http://www.w3.org/2001/XMLSchema#string',
            $fixture->getDatatype()
        );
    }

    /**
     * Tests isBlank
     */
    public function testIsBlank()
    {
        $fixture = $this->newInstance('foo');
        $this->assertFalse($fixture->isBlank());
    }

    /**
     * Tests isConcrete
     */
    public function testIsConcrete()
    {
        $fixture = $this->newInstance(null, null);
        $this->assertTrue($fixture->isConcrete());
        
        $fixture = $this->newInstance('hallo', 'de');
        $this->assertTrue($fixture->isConcrete());
    }

    /**
     * Tests isLiteral
     */
    public function testIsLiteral()
    {
        $fixture = $this->newInstance('hallo', 'de');
        $this->assertTrue($fixture->isLiteral());
    }

    /**
     * Tests isNamed
     */
    public function testIsNamed()
    {
        $fixture = $this->newInstance('hallo', 'de');
        $this->assertFalse($fixture->isNamed());
    }

    /**
     * Tests toNT
     */
    public function testToNTLangAndValueSet()
    {
        $fixture = $this->newInstance('foo', 'en');

        $this->assertEquals('"foo"@en', $fixture->toNQuads());
    }

    public function testToNTValueBoolean()
    {
        $fixture = $this->newInstance(true);

        $this->assertEquals(
            '"true"^^<http://www.w3.org/2001/XMLSchema#boolean>',
            $fixture->toNQuads()
        );
    }

    public function testToNTValueInteger()
    {
        $fixture = $this->newInstance(30);

        $this->assertEquals(
            '"30"^^<http://www.w3.org/2001/XMLSchema#integer>',
            $fixture->toNQuads()
        );
    }

    public function testToNTValueNull()
    {
        // TODO: Implement case for getDatatype when value is null.
        $this->setExpectedException('\Exception');

        $fixture = $this->newInstance(null);

        $fixture->toNQuads();
    }

    public function testToNTValueString()
    {
        $fixture = $this->newInstance('foo');

        $this->assertEquals(
            '"foo"^^<http://www.w3.org/2001/XMLSchema#string>',
            $fixture->toNQuads()
        );
    }

    /**
     * @expectedException \Exception
     */
    final public function testMatchesChecksPatternType()
    {
        $fixture = $this->newInstance('foo', 'en-US');
        // Will fail. Pattern must be of type Literal or Variable
        $fixture->matches(new BlankNodeImpl('foo'));
    }

    final public function testMatches()
    {
        $fixture = $this->newInstance('foo', 'en-US');

        $this->assertTrue($fixture->matches(new VariableImpl('?o')));
        $this->assertTrue($fixture->matches(new LiteralImpl('foo', 'en-US')));
        $this->assertFalse($fixture->matches(new LiteralImpl('foo', 'de')));
        $this->assertFalse($fixture->matches(new LiteralImpl('foo')));
        $this->assertFalse($fixture->matches(new LiteralImpl('bar', 'en-US')));
    }
}
