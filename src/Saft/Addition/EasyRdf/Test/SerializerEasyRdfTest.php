<?php

namespace Saft\Addition\EasyRdf\Test;

use Saft\Addition\EasyRdf\Data\SerializerEasyRdf;
use Saft\Data\Test\SerializerAbstractTest;

class SerializerEasyRdfTest extends SerializerAbstractTest
{
    /**
     * @return Serializer
     */
    protected function newInstance()
    {
        return new SerializerEasyRdf();
    }
}
