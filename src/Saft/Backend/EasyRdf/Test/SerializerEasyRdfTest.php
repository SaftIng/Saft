<?php

namespace Saft\Backend\EasyRdf\Test;

use Saft\Backend\EasyRdf\Data\SerializerEasyRdf;
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
