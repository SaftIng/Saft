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

use Saft\Rdf\BlankNodeImpl;
use Saft\Rdf\Literal;
use Saft\Rdf\NamedNode;

abstract class AbstractNodeFactoryTest extends TestCase
{
    /**
     * An abstract method which returns new instances of NodeFactory.
     */
    abstract public function getFixture();

    public function testCreateNamedNodeShortenedUri()
    {
        $fixture = $this->getFixture();

        $node = $fixture->createNamedNode('foaf:Person');

        $this->assertTrue($node->isNamed());
        $this->assertEquals('http://xmlns.com/foaf/0.1/Person', $node->getUri());
    }

    public function testCreateNamedNodeExtendedUri()
    {
        $fixture = $this->getFixture();

        $node = $fixture->createNamedNode('http://xmlns.com/foaf/0.1/Person');
        $this->assertEquals('http://xmlns.com/foaf/0.1/Person', $node->getUri());
    }

    public function testNamedNodeFromNQuads()
    {
        $fixture = $this->getFixture();

        $node = $fixture->createNodeFromNQuads('<http://example.org/>');

        $this->assertTrue($node->isNamed());
        $this->assertEquals('http://example.org/', $node->getUri());
    }

    public function testLiteralsFromNQuads()
    {
        $fixture = $this->getFixture();

        $node = $fixture->createNodeFromNQuads('"Hallo"');

        $this->assertTrue($node->isLiteral());
        $this->assertEquals('Hallo', $node->getValue());

        $nodeLang = $fixture->createNodeFromNQuads('"Hallo"@de');

        $this->assertTrue($nodeLang->isLiteral());
        $this->assertEquals('Hallo', $nodeLang->getValue());
        $this->assertEquals('de', $nodeLang->getLanguage());

        $nodeTyped = $fixture->createNodeFromNQuads('"Hallo"^^<http://example.org/string>');

        $this->assertTrue($nodeTyped->isLiteral());
        $this->assertEquals('Hallo', $nodeTyped->getValue());

        $datatype = $nodeTyped->getDatatype();
        $this->assertEquals('http://example.org/string', $datatype->getUri());
    }

    public function testBlankNodeFromNQuads()
    {
        $fixture = $this->getFixture();

        $node = $fixture->createNodeFromNQuads('_:1234');

        $this->assertTrue($node->isBlank());
        $this->assertEquals('1234', $node->getBlankId());
    }

    public function testWrongStringFromNQuads()
    {
        $fixture = $this->getFixture();

        $this->setExpectedException('Exception');
        $fixture->createNodeFromNQuads('http://example.org/blabla?argument=value#something');
    }

    /*
     * Tests for createNodeInstanceFromNodeParameter
     */

    public function testCreateNodeInstanceBNode()
    {
        $node = $this->getFixture()->createNodeInstanceFromNodeParameter(
            'bid',
            'bnode'
        );

        $this->assertEquals(new BlankNodeImpl('bid'), $node);
    }

    public function testCreateNodeInstanceLiteral()
    {
        $node = $this->getFixture()->createNodeInstanceFromNodeParameter(
            '42',
            'literal',
            'xsd:int'
        );

        $this->assertTrue($node instanceof Literal);
        $this->assertEquals('42', $node->getValue());
        $this->assertEquals('http://www.w3.org/2001/XMLSchema#int', $node->getDatatype());
    }

    public function testCreateNodeInstanceUnknown()
    {
        // expect exception, because given type is unknown
        $this->setExpectedException('\Exception');

        $node = $this->getFixture()->createNodeInstanceFromNodeParameter(
            null,
            'unknown'
        );
    }

    public function testCreateNodeInstanceUri()
    {
        $node = $this->getFixture()->createNodeInstanceFromNodeParameter(
            'http://foo',
            'uri'
        );

        $this->assertTrue($node instanceof NamedNode);
        $this->assertEquals('http://foo', $node->getUri());
    }
}
