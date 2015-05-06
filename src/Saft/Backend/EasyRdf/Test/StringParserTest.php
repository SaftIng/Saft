<?php

namespace Saft\Backend\EasyRdf\Test;

use Saft\TestCase;
use Saft\Backend\EasyRdf\Data\StringParser;
use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\LiteralImpl;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\StatementImpl;
use Saft\Rdf\StatementFactoryImpl;


use Saft\Rdf\AnyPatternImpl;

class StringParserTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->fixture = new StringParser(new NodeFactoryImpl(), new StatementFactoryImpl());
    }

    /**
     * Tests parse
     */

    public function testParse()
    {
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
                new LiteralImpl('Foo', 'http://www.w3.org/2001/XMLSchema#string')
            ),
            new StatementImpl(
                new NamedNodeImpl('http://saft/example/Bar'),
                new NamedNodeImpl('http://saft/example/name'),
                new LiteralImpl('Bar', 'http://www.w3.org/2001/XMLSchema#string')
            ),
        ));

        $this->assertEquals(
            $statementIteratorToCheckAgainst,
            $this->fixture->parse($testString)
        );
    }
}
