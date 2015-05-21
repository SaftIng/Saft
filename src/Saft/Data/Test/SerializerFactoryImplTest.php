<?php

namespace Saft\Data\Test;

use Saft\Data\SerializerFactoryImpl;

class SerializerFactoryImplTest extends SerializerFactoryAbstractTest
{
    /**
     * This list represents all serializations that are supported by the Serializers behind the ParserFactory
     * class to test.
     *
     * @var array
     */
    protected $availableSerializations = array('nquads', 'ntriples');

    /**
     * @return SerializerFactory
     */
    protected function newInstance()
    {
        return new SerializerFactoryImpl();
    }
}
