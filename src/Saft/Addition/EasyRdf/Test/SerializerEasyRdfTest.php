<?php

/*
 * This file is part of Saft.
 *
 * (c) Konrad Abicht <hi@inspirito.de>
 * (c) Natanael Arndt <arndt@informatik.uni-leipzig.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Saft\Addition\EasyRdf\Test;

use Saft\Addition\EasyRdf\Data\SerializerEasyRdf;
use Saft\Data\Test\AbstractSerializerTest;

class SerializerEasyRdfTest extends AbstractSerializerTest
{
    /**
     * @param string $serialization
     *
     * @return Serializer
     */
    protected function newInstance($serialization)
    {
        return new SerializerEasyRdf($serialization);
    }
}
