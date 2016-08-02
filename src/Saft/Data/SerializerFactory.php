<?php

namespace Saft\Data;

/**
 * The SerializerFactory interface abstract the creation of Serializer instances. It helps you to create a suitable
 * serializer for a given serialization, if available. It also provides a list of supported serializations.
 *
 * @api
 * @package Saft\Data;
 * @since 0.1
 */
interface SerializerFactory
{
    /**
     * Creates a Serializer instance for a given serialization, if available.
     *
     * @param string $serialization The serialization you need a serializer for. In case it is not available,
     *                              an exception will be thrown.
     * @return Parser Suitable serializer for the requested serialization.
     * @throws \Exception if serializer for requested serialization is not available.
     * @api
     * @since 0.1
     */
    public function createSerializerFor($serialization);

    /**
     * Returns a list of all supported serialization types.
     *
     * @return array Array of supported serialization types.
     * @api
     * @since 0.1
     */
    public function getSupportedSerializations();
}
