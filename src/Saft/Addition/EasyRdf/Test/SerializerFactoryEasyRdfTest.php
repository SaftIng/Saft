<?php

namespace Saft\Addition\EasyRdf\Data\Test;

use Saft\Addition\EasyRdf\Data\SerializerFactoryEasyRdf;
use Saft\Data\Test\SerializerFactoryAbstractTest;

class SerializerFactoryEasyRdfTest extends SerializerFactoryAbstractTest
{
    /**
     * This list represents all serializations that are supported by the Serializers behind the ParserFactory
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
     * @return SerializerFactory
     */
    protected function newInstance()
    {
        return new SerializerFactoryEasyRdf();
    }
}
