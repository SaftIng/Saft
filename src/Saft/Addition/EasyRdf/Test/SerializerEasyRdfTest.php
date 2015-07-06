<?php

namespace Saft\Addition\EasyRdf\Test;

use Saft\Addition\EasyRdf\Data\SerializerEasyRdf;
use Saft\Data\Test\SerializerAbstractTest;

class SerializerEasyRdfTest extends SerializerAbstractTest
{
    /**
     * @param string $serialization
     * @return Serializer
     */
    protected function newInstance($serialization)
    {
        return new SerializerEasyRdf($serialization);
    }
}
