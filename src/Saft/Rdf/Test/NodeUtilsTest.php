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
