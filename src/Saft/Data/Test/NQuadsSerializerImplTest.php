<?php

namespace Saft\Data\Test;

use Saft\Data\NQuadsSerializerImpl;
use Saft\Test\TestCase;

class NQuadsSerializerImplTest extends SerializerAbstractTest
{
    /**
     * @return Serializer
     */
    protected function newInstance()
    {
        return new NQuadsSerializerImpl();
    }
}
