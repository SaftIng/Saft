<?php

namespace Saft\Addition\EasyRdf\Data;

use Saft\Data\SerializerFactory;

class SerializerFactoryEasyRdf implements SerializerFactory
{
    /**
     * Creates a Serializer instance for a given serialization, if available.
     *
     * @param  string     $serialization The serialization you need a serializer for. In case it is not
     *                                   available, an exception will be thrown.
     * @return Parser     Suitable serializer for the requested serialization.
     * @throws \Exception If serializer for requested serialization is not available.
     */
    public function createSerializerFor($serialization)
    {
        $serializer = new SerializerEasyRdf();

        if (in_array($serialization, $serializer->getSupportedSerializations())) {
            return $serializer;

        } else {
            throw new \Exception(
                'No serializer for requested serialization available: '. $serialization
            );
        }
    }

    /**
     * Returns a list of all supported serialization types.
     *
     * @return array Array of supported serialization types which can be used by this serializer.
     */
    public function getSupportedSerializations()
    {
        return array('n-triples', 'rdf-json', 'rdf-xml', 'rdfa', 'turtle');
    }
}
