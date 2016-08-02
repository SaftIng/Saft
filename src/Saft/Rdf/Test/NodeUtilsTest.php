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
    public function setUp()
    {
        parent::setUp();

        $this->fixture = new NodeUtils();
    }

    /*
     * Tests for createNodeInstance
     */

    public function testCreateNodeInstanceBNode()
    {
        $node = $this->fixture->createNodeInstance(
            new NodeFactoryImpl(),
            'bid',
            'bnode'
        );

        $this->assertEquals(new BlankNodeImpl('bid'), $node);
    }

    public function testCreateNodeInstanceLiteral()
    {
        $node = $this->fixture->createNodeInstance(
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

        $node = $this->fixture->createNodeInstance(
            new NodeFactoryImpl(),
            null,
            'unknown'
        );
    }

    public function testCreateNodeInstanceUri()
    {
        $node = $this->fixture->createNodeInstance(
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
        $this->assertFalse($this->fixture->simpleCheckURI(''));
        $this->assertFalse($this->fixture->simpleCheckURI('http//foobar/'));

        $this->assertTrue($this->fixture->simpleCheckURI('http:foobar/'));
        $this->assertTrue($this->fixture->simpleCheckURI('http://foobar/'));
        $this->assertTrue($this->fixture->simpleCheckURI('http://foobar:42/'));
        $this->assertTrue($this->fixture->simpleCheckURI('http://foo:bar@foobar/'));
    }
}
