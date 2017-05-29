<?php

namespace Saft\Addition\hardf\Test;

use Saft\Addition\hardf\Data\ParserFactoryHardf;
use Saft\Data\Test\ParserFactoryAbstractTest;
use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\RdfHelpers;
use Saft\Rdf\StatementFactoryImpl;
use Saft\Rdf\StatementIteratorFactoryImpl;

class ParserFactoryHardfTest extends ParserFactoryAbstractTest
{
    /**
     * This list represents all serializations that are supported by the Parsers behind the ParserFactory
     * class to test.
     *
     * @var array
     */
    protected $availableSerializations = array(
        'n-triples',
        'n-quads',
        'turtle'
    );

    /**
     * @return ParserFactory
     */
    protected function newInstance()
    {
        return new ParserFactoryHardf(
            new NodeFactoryImpl(new RdfHelpers()),
            new StatementFactoryImpl(),
            new StatementIteratorFactoryImpl(),
            new RdfHelpers()
        );
    }

    public function testBum()
    {
        $str = '<http://foo> <http://bar> <http://baz> ;
                    <http://bar2> "hey"@de ;
                    <http://bar3> [
                        <http://bar4> "hi"^^<http://xsd#string>
                    ] .';

        $instance = $this->newInstance();
        $parser = $instance->createParserFor('turtle');
        $iterator = $parser->parseStringToIterator($str);
    }
}
