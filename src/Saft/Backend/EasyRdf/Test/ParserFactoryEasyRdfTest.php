<?php

namespace Saft\Backend\EasyRdf\Test;

use Saft\Backend\EasyRdf\Data\ParserFactoryEasyRdf;
use Saft\Data\Test\ParserFactoryAbstractTest;

class ParserFactoryEasyRdfTest extends ParserFactoryAbstractTest
{
    /**
     * This list represents all serializations that are supported by the Parsers behind the ParserFactory
     * class to test.
     *
     * @var array
     */
    protected $availableSerializations = array(
        'json',
        'ntriples',
        'rdfa',
        'rdfxml',
        'sparql-xml',
        'turtle'
    );

    /**
     * @return ParserFactory
     */
    protected function newInstance()
    {
        return new ParserFactoryEasyRdf();
    }
}
