<?php

namespace Saft\Rdf\Test;

use \Saft\Rdf\LiteralImpl;
use \Saft\Rdf\NamedNodeImpl;
use \Saft\Rdf\StatementImpl;
use \Saft\TestCase;

abstract class StatementIteratorAbstractTest extends TestCase
{
    abstract public function createInstanceWithArray(array $statements);

    public function testIterationForeach()
    {
        $statements = [
        new StatementImpl(
            new NamedNodeImpl('http://s/'),
            new NamedNodeImpl('http://p/'),
            new NamedNodeImpl('http://o/')
        ),
        new StatementImpl(
            new NamedNodeImpl('http://s/'),
            new NamedNodeImpl('http://p/'),
            new LiteralImpl('foobar', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#langString', 'en')
        ),
        new StatementImpl(
            new NamedNodeImpl('http://s/'),
            new NamedNodeImpl('http://p/'),
            new LiteralImpl(42)
        )];

        $iterator = $this->createInstanceWithArray($statements);

        $this->assertTrue($iterator->valid());

        $actual = array();
        foreach ($iterator as $key => $value) {
            $actual[] = $value;
        }

        $this->assertEqualsArrays($statements, $actual);
    }

    public function testCountAssertionSome()
    {
        $statements = [
        new StatementImpl(
            new NamedNodeImpl('http://s/'),
            new NamedNodeImpl('http://p/'),
            new NamedNodeImpl('http://o/')
        ),
        new StatementImpl(
            new NamedNodeImpl('http://s/'),
            new NamedNodeImpl('http://p/'),
            new LiteralImpl('foobar', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#langString', 'en')
        ),
        new StatementImpl(
            new NamedNodeImpl('http://s/'),
            new NamedNodeImpl('http://p/'),
            new LiteralImpl(42)
        )];

        $iterator = $this->createInstanceWithArray($statements);

        $this->assertCountStatementIterator(3, $iterator);
    }

    public function testCountAssertionNone()
    {
        $statements = [];

        $iterator = $this->createInstanceWithArray($statements);

        $this->assertCountStatementIterator(0, $iterator);
    }
}
