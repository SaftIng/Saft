<?php

namespace Saft\Rdf\Test;

use Saft\Rdf\NodeUtils;
use Saft\Test\TestCase;

class NodeUtilsTest extends TestCase
{
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
