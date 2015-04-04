<?php
namespace Saft\Rdf\Test;

use Saft\Rdf\StatementImpl;
use Saft\Rdf\VariableImpl;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\LiteralImpl;

abstract class StatementAbstractTest extends \PHPUnit_Framework_TestCase
{
    abstract public function newLiteralInstance($value, $lang = null, $datatype = null);
    abstract public function newNamedNodeInstance($uri);
    abstract public function newVariableInstance($value);
    abstract public function newBlankNodeInstance($id);
    abstract public function newInstance($subject, $predicate, $object, $graph = null);

    public function testNQuadsResource()
    {
        $node = $this->newNamedNodeInstance('http://example.org/test');
        $fixture = $this->newInstance($node, $node, $node);

        $this->assertEquals(
            '<http://example.org/test> <http://example.org/test> <http://example.org/test> .',
            $fixture->toNQuads()
        );
    }

    public function testNQuadsResourceLiteral()
    {
        $node = $this->newNamedNodeInstance('http://example.org/test');
        $literal = $this->newLiteralInstance('http://example.org/test');
        $fixture = $this->newInstance($node, $node, $literal);

        $this->assertEquals(
            '<http://example.org/test> <http://example.org/test> '.
            '"http://example.org/test"^^<http://www.w3.org/2001/XMLSchema#string> .',
            $fixture->toNQuads()
        );
    }

    /**
     * @expectedException \LogicException
     */
    final public function testMatchesChecksIfConcrete()
    {
        $subject = new VariableImpl('?foo');
        $predicate = new VariableImpl('?bar');
        $object = new VariableImpl('?baz');
        $fixture = $this->newInstance($subject, $predicate, $object);

        $subject = new VariableImpl('?s');
        $predicate = new VariableImpl('?p');
        $object = new VariableImpl('?o');
        $pattern = new StatementImpl($subject, $predicate, $object);

        // Should fail while $fixture is not concrete
        $fixture->matches($pattern);
    }

    final public function testMatches()
    {
        $subject = new NamedNodeImpl('http://foo.net');
        $predicate = new NamedNodeImpl('http://bar.net');
        $object = new LiteralImpl('baz');
        $fixture = $this->newInstance($subject, $predicate, $object);

        $subject = new VariableImpl('?s');
        $predicate = new VariableImpl('?p');
        $object = new VariableImpl('?o');
        $pattern = new StatementImpl($subject, $predicate, $object);
        $this->assertTrue($fixture->matches($pattern));

        $subject = new NamedNodeImpl('http://foo.net');
        $predicate = new NamedNodeImpl('http://bar.net');
        $object = new LiteralImpl('baz');
        $pattern = new StatementImpl($subject, $predicate, $object);
        $this->assertTrue($fixture->matches($pattern));

        $subject = new VariableImpl('?s');
        $predicate = new NamedNodeImpl('http://bar.net');
        $object = new VariableImpl('?o');
        $pattern = new StatementImpl($subject, $predicate, $object);
        $this->assertTrue($fixture->matches($pattern));

        $subject = new VariableImpl('?s');
        $predicate = new NamedNodeImpl('http://other.net');
        $object = new VariableImpl('?o');
        $pattern = new StatementImpl($subject, $predicate, $object);
        $this->assertFalse($fixture->matches($pattern));

        $subject = new NamedNodeImpl('http://other.net');
        $predicate = new NamedNodeImpl('http://bar.net');
        $object = new LiteralImpl('baz');
        $pattern = new StatementImpl($subject, $predicate, $object);
        $this->assertFalse($fixture->matches($pattern));
    }
}
