<?php

/*
 * This file is part of Saft.
 *
 * (c) Konrad Abicht <hi@inspirito.de>
 * (c) Natanael Arndt <arndt@informatik.uni-leipzig.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Saft\Rdf\Test;

use Saft\Rdf\Node;

/**
 * This abstract test checks classes implementing the Literal interface for conformity with RDF 1.1
 * {@url http://www.w3.org/TR/rdf11-concepts/#section-Graph-Literal}.
 */
abstract class AbstractLiteralTest extends TestCase
{
    /**
     * An abstract method which returns the subject under test (SUT), in this case an instances of Literal.
     *
     * @param string $value    the literal value
     * @param string $datatype
     *
     * @return Literal returns an instance of Literal as SUT
     */
    abstract public function getInstance($value, Node $datatype = null, $lang = null);

    /**
     * This method returns a node factory to produce additional nodes which can be used e.g. to compare the SUT. This is
     * not ment to get/provide the SUT, please use getInstance() method for that.
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

        $fixtureA = $this->getInstance('true', $xsdBoolean);
        $fixtureB = $this->getInstance('123', $xsdInt);
        $fixtureC = $this->getInstance('true', $xsdString);
        $fixtureD = $this->getInstance('true', $rdfLangString, 'en');

        $this->assertTrue($fixtureA->getDatatype() instanceof Node);
        $this->assertTrue($fixtureA->getDatatype()->isNamed());
        $this->assertTrue($fixtureB->getDatatype() instanceof Node);
        $this->assertTrue($fixtureB->getDatatype()->isNamed());
        $this->assertTrue($fixtureC->getDatatype() instanceof Node);
        $this->assertTrue($fixtureC->getDatatype()->isNamed());
        $this->assertTrue($fixtureD->getDatatype() instanceof Node);
        $this->assertTrue($fixtureD->getDatatype()->isNamed());
    }

    /*
     * Tests equality of instances.
     */

    /**
     * Tests term equality of two Literal instances:.
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

        $fixtureA = $this->getInstance('true');
        $fixtureB = $this->getInstance('true');

        $this->assertTrue($fixtureA->equals($fixtureB));

        $fixtureE = $this->getInstance('1');
        $fixtureF = $this->getInstance('1', $xsdInt);

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
        $fixture = $this->getInstance('true', $xsdBoolean);

        $this->assertEquals($xsdBoolean->getUri(), $fixture->getDatatype()->getUri());
    }

    /**
     * @depends testDatatypeIsNamedNode
     */
    public function testGetDatatypeDecimal()
    {
        $xsdDecimal = $this->getNodeFactory()->createNamedNode('http://www.w3.org/2001/XMLSchema#decimal');
        $fixture = $this->getInstance('3.18', $xsdDecimal);

        $this->assertEquals($xsdDecimal->getUri(), $fixture->getDatatype()->getUri());
    }

    /**
     * @depends testDatatypeIsNamedNode
     */
    public function testGetDatatypeInteger()
    {
        $xsdInt = $this->getNodeFactory()->createNamedNode('http://www.w3.org/2001/XMLSchema#integer');

        $fixture = $this->getInstance('3', $xsdInt);

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

        $fixtureA = $this->getInstance('foo');
        $fixtureB = $this->getInstance('5');
        $fixtureC = $this->getInstance('true');
        $fixtureD = $this->getInstance('false');
        $fixtureE = $this->getInstance('3.1415');

        $this->assertEquals($xsdString->getUri(), $fixtureA->getDatatype()->getUri());
        $this->assertEquals($xsdString->getUri(), $fixtureB->getDatatype()->getUri());
        $this->assertEquals($xsdString->getUri(), $fixtureC->getDatatype()->getUri());
        $this->assertEquals($xsdString->getUri(), $fixtureD->getDatatype()->getUri());
        $this->assertEquals($xsdString->getUri(), $fixtureE->getDatatype()->getUri());
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

        $fixtureA = $this->getInstance('foo', null, 'en-us');
        $fixtureB = $this->getInstance('etwas', null, 'de');
        $fixtureC = $this->getInstance('123', null, 'fr');

        $this->assertEquals($rdfLangString->getUri(), $fixtureA->getDatatype()->getUri());
        $this->assertEquals($rdfLangString->getUri(), $fixtureB->getDatatype()->getUri());
        $this->assertEquals($rdfLangString->getUri(), $fixtureC->getDatatype()->getUri());
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

        $fixture = $this->getInstance('foo', $rdfLangString, 'de');
        $this->assertEquals($rdfLangString->getUri(), $fixture->getDatatype()->getUri());
        $this->assertEquals('de', $fixture->getLanguage());
    }

    public function testInitializationWithLangTagAndWrongDatatype()
    {
        $xsdString = $this->getNodeFactory()->createNamedNode('http://www.w3.org/2001/XMLSchema#string');
        $this->expectException('\Exception');

        $this->getInstance('foo', $xsdString, 'de');
    }

    /*
     * Tests for isBlank
     */
    public function testIsBlank()
    {
        $fixture = $this->getInstance('foo');
        $this->assertFalse($fixture->isBlank());
    }

    /*
     * Tests for isConcrete
     */

    public function testIsConcrete()
    {
        $fixture = $this->getInstance('hallo', null, 'de');
        $this->assertTrue($fixture->isConcrete());
    }

    /*
     * Tests for isLiteral
     */

    public function testIsLiteral()
    {
        $fixture = $this->getInstance('hallo', null, 'de');
        $this->assertTrue($fixture->isLiteral());
    }

    /*
     * Tests for isNamed
     */

    public function testIsNamed()
    {
        $fixture = $this->getInstance('hallo', null, 'de');
        $this->assertFalse($fixture->isNamed());
    }

    /*
     * Tests for isPattern
     */

    public function testIsPattern()
    {
        $fixture = $this->getInstance('hallo');
        $this->assertFalse($fixture->isPattern());
    }

    /*
     * Tests for matches
     */

    public function testMatches()
    {
        $xsdInteger = $this->getNodeFactory()->createNamedNode('http://www.w3.org/2001/XMLSchema#integer');
        $fixtureA = $this->getInstance('true');
        $fixtureB = $this->getInstance('true');

        $this->assertTrue($fixtureA->matches($fixtureB));

        $fixtureE = $this->getInstance('1');
        $fixtureF = $this->getInstance('1', $xsdInteger);

        $this->assertFalse($fixtureE->matches($fixtureF));
    }

    /*
     * Tests for value is string
     */

    public function testValueIsString()
    {
        $fixture = $this->getInstance('1.1245');
        $this->assertTrue(is_string($fixture->getValue()));

        $fixture = $this->getInstance('1');
        $this->assertTrue(is_string($fixture->getValue()));

        $fixture = $this->getInstance('1.1245');
        $this->assertTrue(is_string($fixture->getValue()));

        $fixture = $this->getInstance('true');
        $this->assertTrue(is_string($fixture->getValue()));
    }

    /*
     * Tests for toNTQuads
     */

    public function testToNTLangAndValueSet()
    {
        $fixture = $this->getInstance('foo', null, 'en');

        $this->assertEquals('"foo"@en', $fixture->toNQuads());
    }

    public function testToNTValueBoolean()
    {
        $xsdBoolean = $this->getNodeFactory()->createNamedNode('http://www.w3.org/2001/XMLSchema#boolean');
        $fixture = $this->getInstance('true', $xsdBoolean);

        $this->assertEquals(
            '"true"^^<http://www.w3.org/2001/XMLSchema#boolean>',
            $fixture->toNQuads()
        );
    }

    public function testToNTValueInteger()
    {
        $xsdInteger = $this->getNodeFactory()->createNamedNode('http://www.w3.org/2001/XMLSchema#integer');
        $fixture = $this->getInstance('30', $xsdInteger);

        $this->assertEquals(
            '"30"^^<http://www.w3.org/2001/XMLSchema#integer>',
            $fixture->toNQuads()
        );
    }

    public function testToNTValueString()
    {
        $xsdString = $this->getNodeFactory()->createNamedNode('http://www.w3.org/2001/XMLSchema#string');
        $fixture = $this->getInstance('foo', $xsdString);

        $this->assertEquals(
            '"foo"^^<http://www.w3.org/2001/XMLSchema#string>',
            $fixture->toNQuads()
        );
    }

    public function testToNTEscaping()
    {
        // https://www.w3.org/TR/n-quads/#sec-grammar
        // STRING_LITERAL_QUOTE ::= '"' ([^#x22#x5C#xA#xD] | ECHAR | UCHAR)* '"'
        // #x22 = "
        // #x5C = \
        // #xA = LF
        // #xD = CR

        $fixture = $this->getInstance("foo \" \\ \n \r");

        $this->assertEquals(
            '"foo \" \\\\ \n \r"^^<http://www.w3.org/2001/XMLSchema#string>',
            $fixture->toNQuads()
        );
    }

    /*
     * Tests for toString
     */

    public function testToString()
    {
        $fixture = $this->getInstance('literal');

        $this->assertTrue(is_string((string) $fixture));
        $this->assertTrue(strpos((string) $fixture, $fixture->getValue()) >= 0);
    }

    /*
     * check for literal with datatype and language
     */
    public function testWrongDatatypeLanguageLiteral()
    {
        $this->expectException(\Exception::class);
        $this->nodeFactory->createLiteral('TestLanguage', 'http://www.w3.org/2001/XMLSchema#boolean', 'de');
    }

    /*
     * check for literal with language datatype and no language
     */
    public function testNoLanguageTagLiteral()
    {
        $this->expectException(\Exception::class);
        $this->nodeFactory->createLiteral('TestNoLanguage', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#langString');
    }

    /*
     * check for literal with language datatype and empty language
     */
    public function testEmptyLanguageTagLiteral()
    {
        $this->expectException(\Exception::class);
        $this->nodeFactory->createLiteral('TestEmptyLanguage', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#langString', '');
    }
}
