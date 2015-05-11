<?php

namespace Saft\Data\Test;

use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\LiteralImpl;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\StatementImpl;
use Saft\Test\TestCase;

abstract class ParserAbstractTest extends TestCase
{
    abstract public function getParserInstance();

    public function setUp()
    {
        parent::setUp();

    }

    /**
     * Tests parse
     */

    public function testParseString()
    {
        $nodeFactory = new NodeFactoryImpl();
        $xsdString = $nodeFactory->createNamedNode('http://www.w3.org/2001/XMLSchema#string');

        $fixture = $this->getParserInstance();

        $testString = '@prefix ex: <http://saft/example/> .

            ex:Foo  ex:knows ex:Bar ;
                    ex:name  "Foo"^^<http://www.w3.org/2001/XMLSchema#string> .

            ex:Bar  ex:name  "Bar"^^<http://www.w3.org/2001/XMLSchema#string> .';

        //
        $statementIteratorToCheckAgainst = new ArrayStatementIteratorImpl(array(
            new StatementImpl(
                new NamedNodeImpl('http://saft/example/Foo'),
                new NamedNodeImpl('http://saft/example/knows'),
                new NamedNodeImpl('http://saft/example/Bar')
            ),
            new StatementImpl(
                new NamedNodeImpl('http://saft/example/Foo'),
                new NamedNodeImpl('http://saft/example/name'),
                new LiteralImpl('Foo', $xsdString)
            ),
            new StatementImpl(
                new NamedNodeImpl('http://saft/example/Bar'),
                new NamedNodeImpl('http://saft/example/name'),
                new LiteralImpl('Bar', $xsdString)
            ),
        ));

        $this->assertEquals(
            $statementIteratorToCheckAgainst,
            $fixture->parseStringToIterator($testString)
        );
    }
}
