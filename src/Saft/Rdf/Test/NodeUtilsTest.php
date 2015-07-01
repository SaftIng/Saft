<?php

namespace Saft\Rdf\Test;

use Saft\Rdf\BlankNodeImpl;
use Saft\Rdf\LiteralImpl;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\NodeUtils;
use Saft\Test\TestCase;

class NodeUtilsTest extends TestCase
{
    /*
     * Tests for createNodeInstance
     */

    public function testCreateNodeInstanceBNode()
    {
        $node = NodeUtils::createNodeInstance(
            new NodeFactoryImpl(),
            'bid',
            'bnode'
        );

        $this->assertEquals(new BlankNodeImpl('bid'), $node);
    }

    public function testCreateNodeInstanceLiteral()
    {
        $node = NodeUtils::createNodeInstance(
            new NodeFactoryImpl(),
            '42',
            'literal',
            'xsd:int'
        );

        $this->assertEquals(new LiteralImpl('42', new NamedNodeImpl('xsd:int')), $node);
    }

    public function testCreateNodeInstanceUnknown()
    {
        // expect exception, because given type is unknown
        $this->setExpectedException('\Exception');

        $node = NodeUtils::createNodeInstance(
            new NodeFactoryImpl(),
            null,
            'unknown'
        );
    }

    public function testCreateNodeInstanceUri()
    {
        $node = NodeUtils::createNodeInstance(
            new NodeFactoryImpl(),
            'http://foo',
            'uri'
        );

        $this->assertEquals(new NamedNodeImpl('http://foo'), $node);
    }

    /*
     * Tests for simpleCheckURI
     */

    public function testSimpleCheckURI()
    {
        $this->assertFalse(NodeUtils::simpleCheckURI(''));
        $this->assertFalse(NodeUtils::simpleCheckURI('http//foobar/'));

        $this->assertTrue(NodeUtils::simpleCheckURI('http:foobar/'));
        $this->assertTrue(NodeUtils::simpleCheckURI('http://foobar/'));
        $this->assertTrue(NodeUtils::simpleCheckURI('http://foobar:42/'));
        $this->assertTrue(NodeUtils::simpleCheckURI('http://foo:bar@foobar/'));
    }
}
