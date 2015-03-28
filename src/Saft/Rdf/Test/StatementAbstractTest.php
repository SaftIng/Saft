<?php
namespace Saft\Rdf\Test;

abstract class StatementAbstractTest extends \PHPUnit_Framework_TestCase
{
    abstract public function newLiteralInstance($value, $lang = null, $datatype = null);
    abstract public function newNamedNodeInstance($uri);
    abstract public function newVariableInstance($value);
    abstract public function newBlankNodeInstance($id);
    abstract public function newInstance($subject, $predicate, $object, $graph = null);

    public function testNQuadsResource()
    {
        $node = $this->newNamedNodeInstance("http://example.org/test");
        $fixture = $this->newInstance($node, $node, $node);

        $this->assertEquals(
            "<http://example.org/test> <http://example.org/test> <http://example.org/test> .",
            $fixture->toNQuads()
        );
    }

    public function testNQuadsResourceLiteral()
    {
        $node = $this->newNamedNodeInstance("http://example.org/test");
        $literal = $this->newLiteralInstance("http://example.org/test");
        $fixture = $this->newInstance($node, $node, $literal);

        $this->assertEquals(
            '<http://example.org/test> <http://example.org/test> "http://example.org/test" .',
            $fixture->toNQuads()
        );
    }
}
