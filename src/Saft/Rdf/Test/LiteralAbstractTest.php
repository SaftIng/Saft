<?php

namespace Saft\Rdf\Test;

use Saft\Rdf\BlankNodeImpl;
use Saft\Rdf\LiteralImpl;
use Saft\Rdf\Node;
use Saft\Test\TestCase;

/**
 * This abstract test checks classes implementing the Literal interface for conformity with RDF 1.1
 * {@url http://www.w3.org/TR/rdf11-concepts/#section-Graph-Literal}
 * @package Saft\Rdf\Test
 */
abstract class LiteralAbstractTest extends TestCase
{
    /**
     * An abstract method which returns the subject under test (SUT), in this case an instances of Literal
     *
     * @param string $value the literal value
     * @param string $datatype
     * @return Literal returns an instance of Literal as SUT
     */
    abstract public function newInstance($value, Node $datatype = null, $lang = null);

    /**
     * This method returns a node factory to produce additional nodes which can be used e.g. to compare the SUT. This is
     * not ment to get/provide the SUT, please use newInstance() method for that.
     *
     * @return \Saft\Rdf\NodeFactory
     */
    abstract public function getNodeFactory();

    public function testDatatypeIsNamedNode()
    {
        $xsdBoolean = $this->getNodeFactory()->createNamedNode('http://www.w3.org/2001/XMLSchema#boolean');
        $xsdInt = $this->getNodeFactory()->createNamedNode('http://www.w3.org/2001/XMLSchema#integer');
        $xsdString = $this->getNodeFactory()->createNamedNode('http://www.w3.org/2001/XMLSchema#string');
        $rdfLangString = $this->getNodeFactory()->createNamedNode(
            'http://www.w3.org/1999/02/22-rdf-syntax-ns#langString'
        );

        $fixtureA = $this->newInstance(true);
        $fixtureB = $this->newInstance(true, $xsdBoolean);
        $fixtureC = $this->newInstance("true", $xsdBoolean);
        $fixtureD = $this->newInstance("123", $xsdInt);
        $fixtureE = $this->newInstance(123, $xsdInt);
        $fixtureF = $this->newInstance("true", $xsdString);
        $fixtureG = $this->newInstance("true", $rdfLangString, "en");

        $this->assertTrue($fixtureA->getDatatype() instanceof Node);
        $this->assertTrue($fixtureA->getDatatype()->isNamed());
        $this->assertTrue($fixtureB->getDatatype() instanceof Node);
        $this->assertTrue($fixtureB->getDatatype()->isNamed());
        $this->assertTrue($fixtureC->getDatatype() instanceof Node);
        $this->assertTrue($fixtureC->getDatatype()->isNamed());
        $this->assertTrue($fixtureD->getDatatype() instanceof Node);
        $this->assertTrue($fixtureD->getDatatype()->isNamed());
        $this->assertTrue($fixtureE->getDatatype() instanceof Node);
        $this->assertTrue($fixtureE->getDatatype()->isNamed());
        $this->assertTrue($fixtureF->getDatatype() instanceof Node);
        $this->assertTrue($fixtureF->getDatatype()->isNamed());
        $this->assertTrue($fixtureG->getDatatype() instanceof Node);
        $this->assertTrue($fixtureG->getDatatype()->isNamed());
    }

    /*
     * Tests equality of instances.
     */

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
        $xsdInt = $this->getNodeFactory()->createNamedNode('http://www.w3.org/2001/XMLSchema#integer');

        $fixtureA = $this->newInstance(true);
        $fixtureB = $this->newInstance(true);

        $this->assertTrue($fixtureA->equals($fixtureB));

        $fixtureE = $this->newInstance(1);
        $fixtureF = $this->newInstance(1, $xsdInt);

        $this->assertFalse($fixtureE->equals($fixtureF));
    }

    /*
     * Tests for getDatatype
     */

    /**
     * @depends testDatatypeIsNamedNode
     */
    public function testGetDatatypeBoolean()
    {
        $xsdBoolean = $this->getNodeFactory()->createNamedNode('http://www.w3.org/2001/XMLSchema#boolean');
        $fixture = $this->newInstance(true, $xsdBoolean);

        $this->assertEquals($xsdBoolean->getUri(), $fixture->getDatatype()->getUri());
    }

    /**
     *
     * @depends testDatatypeIsNamedNode
     */
    public function testGetDatatypeDecimal()
    {
        $xsdDecimal = $this->getNodeFactory()->createNamedNode('http://www.w3.org/2001/XMLSchema#decimal');
        $fixture = $this->newInstance(3.18, $xsdDecimal);

        $this->assertEquals($xsdDecimal->getUri(), $fixture->getDatatype()->getUri());
    }

    /**
     * @depends testDatatypeIsNamedNode
     */
    public function testGetDatatypeInteger()
    {
        $xsdInt = $this->getNodeFactory()->createNamedNode('http://www.w3.org/2001/XMLSchema#integer');

        $fixture = $this->newInstance(3, $xsdInt);

        $this->assertEquals($xsdInt->getUri(), $fixture->getDatatype()->getUri());
    }

    /**
     * Test if the datatype is set to {@url http://www.w3.org/2001/XMLSchema#string} if none is given.
     *
     * […] Simple literals are syntactic sugar for abstract syntax literals with the datatype IRI
     * {@url http://www.w3.org/2001/XMLSchema#string.} […]
     *
     * @depends testDatatypeIsNamedNode
     */
    public function testGetDatatypeSimple()
    {
        $xsdString = $this->getNodeFactory()->createNamedNode('http://www.w3.org/2001/XMLSchema#string');

        $fixtureA = $this->newInstance("foo");
        $fixtureB = $this->newInstance("5");
        $fixtureC = $this->newInstance(5);
        $fixtureD = $this->newInstance(true);
        $fixtureE = $this->newInstance(false);
        $fixtureF = $this->newInstance(3.1415);

        $this->assertEquals($xsdString->getUri(), $fixtureA->getDatatype()->getUri());
        $this->assertEquals($xsdString->getUri(), $fixtureB->getDatatype()->getUri());
        $this->assertEquals($xsdString->getUri(), $fixtureC->getDatatype()->getUri());
        $this->assertEquals($xsdString->getUri(), $fixtureD->getDatatype()->getUri());
        $this->assertEquals($xsdString->getUri(), $fixtureE->getDatatype()->getUri());
        $this->assertEquals($xsdString->getUri(), $fixtureF->getDatatype()->getUri());
    }

    /**
     * Test if the datatype is set to {@url http://www.w3.org/1999/02/22-rdf-syntax-ns#langString} if the literal is
     * language tagged.
     *
     * […] Similarly, most concrete syntaxes represent language-tagged strings without the datatype IRI because it
     * always equals {@url http://www.w3.org/1999/02/22-rdf-syntax-ns#langString}. […]
     *
     * @depends testDatatypeIsNamedNode
     */
    public function testGetDatatypeLangTagged()
    {
        $rdfLangString = $this->getNodeFactory()->createNamedNode(
            'http://www.w3.org/1999/02/22-rdf-syntax-ns#langString'
        );

        $fixtureA = $this->newInstance('foo', null, "en-us");
        $fixtureB = $this->newInstance("etwas", null, "de");
        $fixtureC = $this->newInstance("123", null, "fr");

        $this->assertEquals($rdfLangString->getUri(), $fixtureA->getDatatype()->getUri());
        $this->assertEquals($rdfLangString->getUri(), $fixtureB->getDatatype()->getUri());
        $this->assertEquals($rdfLangString->getUri(), $fixtureC->getDatatype()->getUri());
    }

    /*
     * These assertions are specific to the PHP implementation and not necessarily implied by the
     * RDF 1.1 standard.
     */

    public function testImplementationSpecificEquality()
    {
        $xsdBoolean = $this->getNodeFactory()->createNamedNode('http://www.w3.org/2001/XMLSchema#boolean');

        $fixtureA = $this->newInstance(true);
        $fixtureB = $this->newInstance(true, $xsdBoolean);
        $fixtureC = $this->newInstance("true", $xsdBoolean);

        $this->assertFalse($fixtureA->equals($fixtureB));
        $this->assertTrue($fixtureB->equals($fixtureC));

        $fixtureD = $this->newInstance(1);
        $fixtureE = $this->newInstance(1.0);

        $this->assertTrue($fixtureD->equals($fixtureE));
    }

    /*
     * Tests for initialization
     */

    /**
     * @depends testDatatypeIsNamedNode
     */
    public function testInitializationWithLangTag()
    {
        $rdfLangString = $this->getNodeFactory()->createNamedNode(
            'http://www.w3.org/1999/02/22-rdf-syntax-ns#langString'
        );

        $fixture = $this->newInstance("foo", $rdfLangString, "de");
        $this->assertEquals($rdfLangString->getUri(), $fixture->getDatatype()->getUri());
        $this->assertEquals('de', $fixture->getLanguage());
    }

    public function testInitializationWithLangTagAndWrongDatatype()
    {
        $xsdString = $this->getNodeFactory()->createNamedNode('http://www.w3.org/2001/XMLSchema#string');
        $this->setExpectedException('\Exception');

        $this->newInstance("foo", $xsdString, "de");
    }

    public function testInitializationWithWrongDatatypeType()
    {
        $this->setExpectedException('PHPUnit_Framework_Error');

        // Should result in a PHP error becauseof wrong argument type
        $this->newInstance("foo", "http://www.w3.org/2001/XMLSchema#string");
    }

    // instanciation with null shouldn't be possible
    public function testInitializationUsingNull()
    {
        $this->setExpectedException('\Exception');

        $this->newInstance(null);
    }

    /*
     * Tests for isBlank
     */
    public function testIsBlank()
    {
        $fixture = $this->newInstance('foo');
        $this->assertFalse($fixture->isBlank());
    }

    /*
     * Tests for isConcrete
     */

    public function testIsConcrete()
    {
        $fixture = $this->newInstance('hallo', null, 'de');
        $this->assertTrue($fixture->isConcrete());
    }

    /*
     * Tests for isLiteral
     */

    public function testIsLiteral()
    {
        $fixture = $this->newInstance('hallo', null, 'de');
        $this->assertTrue($fixture->isLiteral());
    }

    /*
     * Tests for isNamed
     */

    public function testIsNamed()
    {
        $fixture = $this->newInstance('hallo', null, 'de');
        $this->assertFalse($fixture->isNamed());
    }

    /*
     * Tests for isVariable
     */

    public function testIsVariable()
    {
        $fixture = $this->newInstance('hallo');
        $this->assertFalse($fixture->isVariable());
    }

    /*
     * Tests for matches
     */

    public function testMatches()
    {
        $xsdInteger = $this->getNodeFactory()->createNamedNode('http://www.w3.org/2001/XMLSchema#integer');
        $fixtureA = $this->newInstance(true);
        $fixtureB = $this->newInstance(true);

        $this->assertTrue($fixtureA->matches($fixtureB));

        $fixtureE = $this->newInstance(1);
        $fixtureF = $this->newInstance(1, $xsdInteger);

        $this->assertFalse($fixtureE->matches($fixtureF));
    }

    /*
     * Tests for value is string
     */

    public function testValueIsString()
    {
        $fixture = $this->newInstance(1);
        $this->assertTrue(is_string($fixture->getValue()));

        $fixture = $this->newInstance(1.1245);
        $this->assertTrue(is_string($fixture->getValue()));

        $fixture = $this->newInstance("1");
        $this->assertTrue(is_string($fixture->getValue()));

        $fixture = $this->newInstance("1.1245");
        $this->assertTrue(is_string($fixture->getValue()));

        $fixture = $this->newInstance("true");
        $this->assertTrue(is_string($fixture->getValue()));

        $fixture = $this->newInstance(true);
        $this->assertTrue(is_string($fixture->getValue()));
    }

    /*
     * Tests for toNTQuads
     */

    public function testToNTLangAndValueSet()
    {
        $fixture = $this->newInstance('foo', null, 'en');

        $this->assertEquals('"foo"@en', $fixture->toNQuads());
    }

    public function testToNTValueBoolean()
    {
        $xsdBoolean = $this->getNodeFactory()->createNamedNode('http://www.w3.org/2001/XMLSchema#boolean');
        $fixture = $this->newInstance(true, $xsdBoolean);

        $this->assertEquals(
            '"true"^^<http://www.w3.org/2001/XMLSchema#boolean>',
            $fixture->toNQuads()
        );
    }

    public function testToNTValueInteger()
    {
        $xsdInteger = $this->getNodeFactory()->createNamedNode('http://www.w3.org/2001/XMLSchema#integer');
        $fixture = $this->newInstance(30, $xsdInteger);

        $this->assertEquals(
            '"30"^^<http://www.w3.org/2001/XMLSchema#integer>',
            $fixture->toNQuads()
        );
    }

    public function testToNTValueString()
    {
        $xsdString = $this->getNodeFactory()->createNamedNode('http://www.w3.org/2001/XMLSchema#string');
        $fixture = $this->newInstance('foo', $xsdString);

        $this->assertEquals(
            '"foo"^^<http://www.w3.org/2001/XMLSchema#string>',
            $fixture->toNQuads()
        );
    }

    /*
     * Tests for toString
     */

    public function testToString()
    {
        $fixture = $this->newInstance('literal');

        $this->assertTrue(is_string((string)$fixture));
        $this->assertTrue(strpos((string)$fixture, $fixture->getValue()) >= 0);
    }
}
