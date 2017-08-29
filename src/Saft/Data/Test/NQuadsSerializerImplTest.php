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

    public function testSetPrefixes()
    {
        $this->setExpectedException('\Exception');

        $this->fixture = $this->newInstance('n-triples');
        $this->fixture->setPrefixes(array());
    }
}
