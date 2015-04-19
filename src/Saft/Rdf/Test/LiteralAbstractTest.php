<?php
namespace Saft\Rdf\Test;

use Saft\Rdf\VariableImpl;
use Saft\Rdf\LiteralImpl;
use Saft\Rdf\BlankNodeImpl;

/**
 * This abstract test checks classes implementing the Literal interface for conformity with RDF 1.1
 * {@url http://www.w3.org/TR/rdf11-concepts/#section-Graph-Literal}
 * @package Saft\Rdf\Test
 */
abstract class LiteralAbstractTest extends \PHPUnit_Framework_TestCase
{
    /**
     * An abstract method which returns new instances of Literal
     * @todo The factory method approach could also be extended to use a factory object
     */
    abstract public function newInstance($value, $datatype = null, $lang = null);

    /**
     * Tests term equality of two Literal instances:
     *
     * Literal term equality: Two literals are term-equal (the same RDF literal) if and only if the two lexical forms,
     * the two datatype IRIs, and the two language tags (if any) compare equal, character by character. Thus, two
     * literals can have the same value without being the same RDF term. For example:
     *
     *     "1"^^xs:integer
     *     "01"^^xs:integer
     *
     * denote the same value, but are not the same literal RDF terms and are not term-equal because their lexical form
     * differs.
     */
    public function testEquality()
    {
        $fixtureA = $this->newInstance(true);
        $fixtureB = $this->newInstance(true);

        $this->assertTrue($fixtureA->equals($fixtureB));

        $fixtureE = $this->newInstance(1);
        $fixtureF = $this->newInstance(1, 'http://www.w3.org/2001/XMLSchema#integer');

        $this->assertFalse($fixtureE->equals($fixtureF));
    }

    /**
     * These assertions are specific to the PHP implementation and not necessarily implied by the RDF 1.1 standard.
     */
    public function testImplementationSpecificEquality()
    {
        $fixtureA = $this->newInstance(true);
        $fixtureB = $this->newInstance(true, 'http://www.w3.org/2001/XMLSchema#boolean');
        $fixtureC = $this->newInstance("true", 'http://www.w3.org/2001/XMLSchema#boolean');

        $this->assertFalse($fixtureA->equals($fixtureB));
        $this->assertTrue($fixtureB->equals($fixtureC));

        $fixtureD = $this->newInstance(1);
        $fixtureE = $this->newInstance(1.0);

        $this->assertTrue($fixtureD->equals($fixtureE));
    }

    /**
     * Tests getDatatype
     */
    public function testGetDatatypeBoolean()
    {
        $fixture = $this->newInstance(true, 'http://www.w3.org/2001/XMLSchema#boolean');

        $this->assertEquals('http://www.w3.org/2001/XMLSchema#boolean', $fixture->getDatatype());
    }

    public function testGetDatatypeDecimal()
    {
        $fixture = $this->newInstance(3.18, 'http://www.w3.org/2001/XMLSchema#decimal');

        $this->assertEquals(
            'http://www.w3.org/2001/XMLSchema#decimal',
            $fixture->getDatatype()
        );
    }

    public function testGetDatatypeInteger()
    {
        $fixture = $this->newInstance(3, 'http://www.w3.org/2001/XMLSchema#integer');

        $this->assertEquals(
            'http://www.w3.org/2001/XMLSchema#integer',
            $fixture->getDatatype()
        );
    }

    /**
     * Test if the datatype is set to {@url http://www.w3.org/2001/XMLSchema#string} if none is given.
     *
     * […] Simple literals are syntactic sugar for abstract syntax literals with the datatype IRI
     * {@url http://www.w3.org/2001/XMLSchema#string.} […]
     */
    public function testGetDatatypeSimple()
    {
        $fixtureA = $this->newInstance("foo");
        $fixtureB = $this->newInstance("5");
        $fixtureC = $this->newInstance(5);
        $fixtureD = $this->newInstance(true);
        $fixtureE = $this->newInstance(false);
        $fixtureF = $this->newInstance(3.1415);

        $this->assertEquals('http://www.w3.org/2001/XMLSchema#string', $fixtureA->getDatatype());
        $this->assertEquals('http://www.w3.org/2001/XMLSchema#string', $fixtureB->getDatatype());
        $this->assertEquals('http://www.w3.org/2001/XMLSchema#string', $fixtureC->getDatatype());
        $this->assertEquals('http://www.w3.org/2001/XMLSchema#string', $fixtureD->getDatatype());
        $this->assertEquals('http://www.w3.org/2001/XMLSchema#string', $fixtureE->getDatatype());
        $this->assertEquals('http://www.w3.org/2001/XMLSchema#string', $fixtureF->getDatatype());
    }

    /**
     * Test if the datatype is set to {@url http://www.w3.org/1999/02/22-rdf-syntax-ns#langString} if the literal is
     * language tagged.
     *
     * […] Similarly, most concrete syntaxes represent language-tagged strings without the datatype IRI because it
     * always equals {@url http://www.w3.org/1999/02/22-rdf-syntax-ns#langString}. […]
     */
    public function testGetDatatypeLangTagged()
    {
        $fixtureA = $this->newInstance('foo', null, "en-us");
        $fixtureB = $this->newInstance("etwas", null, "de");
        $fixtureC = $this->newInstance("123", null, "fr");

        $this->assertEquals('http://www.w3.org/1999/02/22-rdf-syntax-ns#langString', $fixtureA->getDatatype());
        $this->assertEquals('http://www.w3.org/1999/02/22-rdf-syntax-ns#langString', $fixtureB->getDatatype());
        $this->assertEquals('http://www.w3.org/1999/02/22-rdf-syntax-ns#langString', $fixtureC->getDatatype());
    }

    public function testInitializationWithLangTag()
    {
        $fixture = $this->newInstance("foo", "http://www.w3.org/1999/02/22-rdf-syntax-ns#langString", "de");
        $this->assertEquals('http://www.w3.org/1999/02/22-rdf-syntax-ns#langString', $fixture->getDatatype());
        $this->assertEquals('de', $fixture->getLanguage());
    }

    public function testInitializationWithLangTagAndWrongDatatype()
    {
        $this->setExpectedException('\Exception');

        $fixture = $this->newInstance("foo", "http://www.w3.org/2001/XMLSchema#string", "de");
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
        $fixture = $this->newInstance('hallo', null, 'de');
        $this->assertTrue($fixture->isConcrete());
    }

    /**
     * Tests isLiteral
     */
    public function testIsLiteral()
    {
        $fixture = $this->newInstance('hallo', null, 'de');
        $this->assertTrue($fixture->isLiteral());
    }

    /**
     * Tests isNamed
     */
    public function testIsNamed()
    {
        $fixture = $this->newInstance('hallo', null, 'de');
        $this->assertFalse($fixture->isNamed());
    }


    /**
     * instanciation with null shouldn't be possible
     */
    public function testInstanciationNull()
    {
        $this->setExpectedException('\Exception');

        $this->newInstance(null);
    }

    /**
     * Tests toNT
     */
    public function testToNTLangAndValueSet()
    {
        $fixture = $this->newInstance('foo', null, 'en');

        $this->assertEquals('"foo"@en', $fixture->toNQuads());
    }

    public function testToNTValueBoolean()
    {
        $fixture = $this->newInstance(true, "http://www.w3.org/2001/XMLSchema#boolean");

        $this->assertEquals(
            '"true"^^<http://www.w3.org/2001/XMLSchema#boolean>',
            $fixture->toNQuads()
        );
    }

    public function testToNTValueInteger()
    {
        $fixture = $this->newInstance(30, "http://www.w3.org/2001/XMLSchema#integer");

        $this->assertEquals(
            '"30"^^<http://www.w3.org/2001/XMLSchema#integer>',
            $fixture->toNQuads()
        );
    }

    public function testToNTValueString()
    {
        $fixture = $this->newInstance('foo', "http://www.w3.org/2001/XMLSchema#string");

        $this->assertEquals(
            '"foo"^^<http://www.w3.org/2001/XMLSchema#string>',
            $fixture->toNQuads()
        );
    }

    public function testMatches()
    {
        $fixture = $this->newInstance('foo', null, 'en-US');

        $this->assertTrue($fixture->matches(new VariableImpl('?o')));
        $this->assertTrue($fixture->matches(new LiteralImpl('foo', null, 'en-US')));
        $this->assertFalse($fixture->matches(new LiteralImpl('foo', null, 'de')));
        $this->assertFalse($fixture->matches(new LiteralImpl('foo')));
        $this->assertFalse($fixture->matches(new LiteralImpl('bar', null, 'en-US')));
        $this->assertFalse($fixture->matches(new BlankNodeImpl('foo')));
    }

    public function testToString()
    {
        $fixture = $this->newInstance('literal');

        $this->assertTrue(is_string((string)$fixture));
        $this->assertTrue(strpos((string)$fixture, $fixture->getValue()) >= 0);
    }
}
