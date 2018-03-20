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

namespace Saft\Addition\EasyRdf\Data;

use Saft\Data\SerializerFactory;

class SerializerFactoryEasyRdf implements SerializerFactory
{
    /**
     * Creates a Serializer instance for a given serialization, if available.
     *
     * @param string $serialization The serialization you need a serializer for. In case it is not available,
     *                              an exception will be thrown.
     *
     * @return Parser suitable serializer for the requested serialization
     *
     * @throws \Exception if serializer for requested serialization is not available
     */
    public function createSerializerFor($serialization)
    {
        $serializer = new SerializerEasyRdf($serialization);

        if (in_array($serialization, $serializer->getSupportedSerializations())) {
            return $serializer;
        } else {
            throw new \Exception(
                'No serializer for requested serialization available: '.$serialization.'. '.
                'Supported serializations are: '.implode(', ', $this->getSupportedSerializations())
            );
        }
    }

    /**
     * Returns a list of all supported serialization types.
     *
     * @return array array of supported serialization types which can be used by this serializer
     */
    public function getSupportedSerializations()
    {
        return ['n-triples', 'rdf-json', 'rdf-xml', 'rdfa', 'turtle'];
    }
}
