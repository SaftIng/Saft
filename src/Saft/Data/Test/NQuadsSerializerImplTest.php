<?php

namespace Saft\Data\Test;

use Saft\Data\NQuadsSerializerImpl;
use Saft\Test\TestCase;

class NQuadsSerializerImplTest extends SerializerAbstractTest
{
    /**
     * @param string $serialization
     * @return Serializer
     */
    protected function newInstance($serialization)
    {
        return new NQuadsSerializerImpl($serialization);
    }
}
