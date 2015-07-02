<?php

namespace Saft\Addition\EasyRdf\Test;

use Saft\Addition\EasyRdf\Data\ParserFactoryEasyRdf;
use Saft\Data\Test\ParserFactoryAbstractTest;
use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\StatementFactoryImpl;

class ParserFactoryEasyRdfTest extends ParserFactoryAbstractTest
{
    /**
     * This list represents all serializations that are supported by the Parsers behind the ParserFactory
     * class to test.
     *
     * @var array
     */
    protected $availableSerializations = array(
        'n-triples',
        'rdf-json',
        'rdf-xml',
        'rdfa',
        'turtle'
    );

    /**
     * @return ParserFactory
     */
    protected function newInstance()
    {
        return new ParserFactoryEasyRdf(new NodeFactoryImpl(), new StatementFactoryImpl());
    }
}
