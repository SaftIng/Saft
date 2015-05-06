<?php
namespace Saft\Data\Test;

use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\LiteralImpl;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\StatementImpl;

abstract class ParserAbstractTest extends \PHPUnit_Framework_TestCase
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
            $fixture->parseStringToIterator($testString)
        );
    }
}
