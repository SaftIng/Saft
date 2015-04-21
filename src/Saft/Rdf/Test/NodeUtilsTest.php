<?php
namespace Saft\Rdf\Test;

use Saft\Rdf\NodeUtils;

class NodeUtilsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests simpleCheckURI
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
