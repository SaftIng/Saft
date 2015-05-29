<?php

namespace Saft\Rdf\Test;

use Saft\Test\TestCase;

abstract class NodeFactoryAbstractTest extends TestCase
{

    /**
     * An abstract method which returns new instances of NodeFactory
     */
    abstract public function getFixture();

    public function testNamedNodeFromNQuads()
    {
        $fixture = $this->getFixture();

        $node = $fixture->createNodeFromNQuads("<http://example.org/>");

        $this->assertTrue($node->isNamed());
        $this->assertEquals("http://example.org/", $node->getUri());
    }

    public function testLiteralsFromNQuads()
    {
        $fixture = $this->getFixture();

        $node = $fixture->createNodeFromNQuads('"Hallo"');

        $this->assertTrue($node->isLiteral());
        $this->assertEquals("Hallo", $node->getValue());

        $nodeLang = $fixture->createNodeFromNQuads('"Hallo"@de');

        $this->assertTrue($nodeLang->isLiteral());
        $this->assertEquals("Hallo", $nodeLang->getValue());
        $this->assertEquals("de", $nodeLang->getLanguage());

        $nodeTyped = $fixture->createNodeFromNQuads('"Hallo"^^<http://example.org/string>');

        $this->assertTrue($nodeTyped->isLiteral());
        $this->assertEquals("Hallo", $nodeTyped->getValue());

        $datatype = $nodeTyped->getDatatype();
        $this->assertEquals("http://example.org/string", $datatype->getUri());
    }

    public function testBlankNodeFromNQuads()
    {
        $fixture = $this->getFixture();

        $node = $fixture->createNodeFromNQuads('_:1234');

        $this->assertTrue($node->isBlank());
        $this->assertEquals("1234", $node->getBlankId());
    }
}
