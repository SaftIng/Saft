<?php

namespace Saft\Rdf;

class LiteralTest extends \PHPUnit_Framework_TestCase
{

    /**
     *
     */
    public function setUp()
    {
        parent::setUp();

        $this->fixture = new \Saft\Rdf\LiteralImpl(null, null);
    }

    /**
     *
     */
    public function tearDown()
    {
        parent::tearDown();
    }

    /**
     * Tests equals
     */
    public function testEquals()
    {
        $this->fixture = new \Saft\Rdf\LiteralImpl(true);
        $toCompare = new \Saft\Rdf\LiteralImpl(true);

        $this->assertTrue($this->fixture->equals($toCompare));
    }

    public function testEqualsDifferentType()
    {
        $this->fixture = new \Saft\Rdf\LiteralImpl(1);
        $toCompare = new \Saft\Rdf\LiteralImpl(1.0);

        $this->assertFalse($this->fixture->equals($toCompare));
    }

    /**
     * Tests getDatatype
     */
    public function testGetDatatypeBoolean()
    {
        $this->fixture = new \Saft\Rdf\LiteralImpl(true);

        $this->assertEquals(
            'http://www.w3.org/2001/XMLSchema#boolean',
            $this->fixture->getDatatype()
        );
    }

    public function testGetDatatypeLangSet()
    {
        $this->fixture = new \Saft\Rdf\LiteralImpl('foo', 'en');

        $this->assertNull($this->fixture->getDatatype());
    }

    public function testGetDatatypeDecimal()
    {
        $this->fixture = new \Saft\Rdf\LiteralImpl(3.18);

        $this->assertEquals(
            'http://www.w3.org/2001/XMLSchema#decimal',
            $this->fixture->getDatatype()
        );
    }

    public function testGetDatatypeInteger()
    {
        $this->fixture = new \Saft\Rdf\LiteralImpl(3);

        $this->assertEquals(
            'http://www.w3.org/2001/XMLSchema#integer',
            $this->fixture->getDatatype()
        );
    }

    public function testGetDatatypeString()
    {
        $this->fixture = new \Saft\Rdf\LiteralImpl('foo');

        $this->assertEquals(
            'http://www.w3.org/2001/XMLSchema#string',
            $this->fixture->getDatatype()
        );
    }

    /**
     * Tests isBlank
     */
    public function testIsBlank()
    {
        $this->assertFalse($this->fixture->isBlank());
    }

    /**
     * Tests isConcrete
     */
    public function testIsConcrete()
    {
        $this->fixture = new \Saft\Rdf\LiteralImpl(null, null);
        $this->assertTrue($this->fixture->isConcrete());
        
        $this->fixture = new \Saft\Rdf\LiteralImpl('hallo', 'de');
        $this->assertTrue($this->fixture->isConcrete());
    }

    /**
     * Tests isLiteral
     */
    public function testIsLiteral()
    {
        $this->assertTrue($this->fixture->isLiteral());
    }

    /**
     * Tests isNamed
     */
    public function testIsNamed()
    {
        $this->assertFalse($this->fixture->isNamed());
    }

    /**
     * Tests toNT
     */
    public function testToNTLangAndValueSet()
    {
        $this->fixture = new \Saft\Rdf\LiteralImpl('foo', 'en');

        $this->assertEquals('"foo"@en', $this->fixture->toNQuads());
    }

    public function testToNTValueBoolean()
    {
        $this->fixture = new \Saft\Rdf\LiteralImpl(true);

        $this->assertEquals(
            '"true"^^<http://www.w3.org/2001/XMLSchema#boolean>',
            $this->fixture->toNQuads()
        );
    }

    public function testToNTValueInteger()
    {
        $this->fixture = new \Saft\Rdf\LiteralImpl(30);

        $this->assertEquals(
            '"30"^^<http://www.w3.org/2001/XMLSchema#integer>',
            $this->fixture->toNQuads()
        );
    }

    public function testToNTValueNull()
    {
        // TODO: Implement case for getDatatype when value is null.
        $this->setExpectedException('\Exception');

        $this->fixture = new \Saft\Rdf\LiteralImpl(null);

        $this->fixture->toNQuads();
    }

    public function testToNTValueString()
    {
        $this->fixture = new \Saft\Rdf\LiteralImpl('foo');

        $this->assertEquals(
            '"foo"^^<http://www.w3.org/2001/XMLSchema#string>',
            $this->fixture->toNQuads()
        );
    }
}
