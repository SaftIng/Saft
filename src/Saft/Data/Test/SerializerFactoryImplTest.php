<?php

namespace Saft\Data\Test;

use Saft\Data\SerializerFactoryImpl;

class SerializerFactoryImplTest extends SerializerFactoryAbstractTest
{
    /**
     * @return SerializerFactory
     */
    protected function newInstance()
    {
        return new SerializerFactoryImpl();
    }
}
