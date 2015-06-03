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
    /**
     * @return Parser
     */
    abstract protected function newInstance();

    /*
     * Tests for getSupportedSerializations
     */

    // TODO what else can we test here?
    public function testGetSupportedSerializations()
    {
        $this->assertTrue(is_array($this->newInstance()->getSupportedSerializations()));
    }

    /*
     * Tests for parseStreamToIterator
     */

    // we load here the content of a turtle file and transform it into an StatementIterator instance.
    // afterwards we check if the read data are the same as expected.
    public function testParseStreamToIteratorTurtleFile()
    {
        $this->fixture = $this->newInstance();

        // load iterator for a turtle file
        $inputStream = dirname(__FILE__) .'/../resources/example.ttl';
        $iterator = $this->fixture->parseStreamToIterator($inputStream);

        $statementIteratorToCheckAgainst = new ArrayStatementIteratorImpl(array(
            new StatementImpl(
                new NamedNodeImpl('http://saft/example/'),
                new NamedNodeImpl('http://www.w3.org/1999/02/22-rdf-syntax-ns#type'),
                new NamedNodeImpl('http://saft/example/Foo')
            ),
            new StatementImpl(
                new NamedNodeImpl('http://saft/example/'),
                new NamedNodeImpl('http://www.w3.org/2000/01/rdf-schema#label'),
                new LiteralImpl('RDFS label')
            ),
            new StatementImpl(
                new NamedNodeImpl('http://saft/example/Foobar'),
                new NamedNodeImpl('http://www.w3.org/1999/02/22-rdf-syntax-ns#type'),
                new NamedNodeImpl('http://saft/example/Bar')
            ),
            new StatementImpl(
                new NamedNodeImpl('http://saft/example/Foobar'),
                new NamedNodeImpl('http://www.w3.org/2000/01/rdf-schema#label'),
                new LiteralImpl(
                    'RDFS label with language tag',
                    new NamedNodeImpl('http://www.w3.org/1999/02/22-rdf-syntax-ns#langString'),
                    'en'
                )
            ),
            new StatementImpl(
                new NamedNodeImpl('http://saft/example/Foobar'),
                new NamedNodeImpl('http://www.w3.org/2000/01/rdf-schema#comment'),
                new LiteralImpl("\n    Multi line comment\n    ")
            ),
            new StatementImpl(
                new NamedNodeImpl('http://saft/example/Foobar'),
                new NamedNodeImpl('http://saft/example/component'),
                new NamedNodeImpl("http://saft/example/geo")
            ),
            new StatementImpl(
                new NamedNodeImpl('http://saft/example/Foobar'),
                new NamedNodeImpl('http://saft/example/component'),
                new NamedNodeImpl("http://saft/example/time")
            ),
            new StatementImpl(
                new NamedNodeImpl('http://saft/example/dataset'),
                new NamedNodeImpl('http://www.w3.org/2000/01/rdf-schema#label'),
                new LiteralImpl(
                    "RDFS label with datatype",
                    new NamedNodeImpl('http://www.w3.org/2001/XMLSchema#string')
                )
            ),
        ));

        $this->assertEquals($statementIteratorToCheckAgainst, $iterator);
    }

    /*
     * Tests for parseStringToIterator
     */

    public function testParseStringToIteratorTurtleString()
    {
        $xsdString = new NamedNodeImpl('http://www.w3.org/2001/XMLSchema#string');

        $fixture = $this->newInstance();

        $testString = '@prefix ex: <http://saft/example/> .
            ex:Foo  ex:knows ex:Bar ; ex:name  "Foo"^^<'. $xsdString .'> .
            ex:Bar  ex:name  "Bar"^^<'. $xsdString .'> .';

        // build StatementIterator to check against
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

        $this->assertEquals($statementIteratorToCheckAgainst, $fixture->parseStringToIterator($testString));
    }
}
