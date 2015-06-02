<?php

namespace Saft\Rdf\Test;

use Saft\Rdf\AnyPatternImpl;
use Saft\Rdf\LiteralImpl;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\StatementImpl;
use Saft\Test\TestCase;

abstract class StatementIteratorAbstractTest extends TestCase
{
    /**
     * @param  array $statements
     * @return StatementIterator
     */
    abstract public function createInstanceWithArray(array $statements);

    /*
     * Tests for constructor
     */

    public function testConstructorValidList()
    {
        // empty array must be fine
        $this->fixture = $this->createInstanceWithArray(array());

        // array with Statement instance must be fine
        $this->fixture = $this->createInstanceWithArray(
            array(new StatementImpl(new AnyPatternImpl(), new AnyPatternImpl(), new AnyPatternImpl()))
        );
    }

    public function testConstructorInvalidList()
    {
        // expect exception, because array contains non-Statement instance
        $this->setExpectedException('\Exception');

        $this->fixture = $this->createInstanceWithArray(array(1));
    }

    /*
     * Tests for count
     */

    public function testCountAssertionSome()
    {
        $nodeFactory = new NodeFactoryImpl();
        $rdfLangString = $nodeFactory->createNamedNode(
            'http://www.w3.org/1999/02/22-rdf-syntax-ns#langString'
        );
        $statements = [
        new StatementImpl(
            new NamedNodeImpl('http://s/'),
            new NamedNodeImpl('http://p/'),
            new NamedNodeImpl('http://o/')
        ),
        new StatementImpl(
            new NamedNodeImpl('http://s/'),
            new NamedNodeImpl('http://p/'),
            new LiteralImpl('foobar', $rdfLangString, 'en')
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

    /*
     * Tests iteration
     */

    public function testIterationWithForeachLoop()
    {
        $nodeFactory = new NodeFactoryImpl();
        $rdfLangString = $nodeFactory->createNamedNode(
            'http://www.w3.org/1999/02/22-rdf-syntax-ns#langString'
        );
        $statements = [
        new StatementImpl(
            new NamedNodeImpl('http://s/'),
            new NamedNodeImpl('http://p/'),
            new NamedNodeImpl('http://o/')
        ),
        new StatementImpl(
            new NamedNodeImpl('http://s/'),
            new NamedNodeImpl('http://p/'),
            new LiteralImpl('foobar', $rdfLangString, 'en')
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
}
