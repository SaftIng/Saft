<?php

namespace Saft\Data;

/**
 * The ParserFactory interface abstract the creation of Parser instances. It helps you to create a suitable parser
 * for a given serialization, if available. It also provides a list of supported serializations.
 *
 * @api
 * @package Saft\Data;
 * @since 0.1
 */
interface ParserFactory
{
    /**
     * Creates a Parser instance for a given format, if available.
     *
     * @param string $serialization The serialization you need a parser for.
     * @return Parser Suitable parser for the requested format.
     * @throws \Exception if a parser for requested serialization is not available.
     * @api
     * @since 0.1
     */
    public function createParserFor($serialization);

    /**
     * Returns an array which contains supported serializations.
     *
     * @return array Array of supported serializations which are understood by this parser.
     * @api
     * @since 0.1
     */
    public function getSupportedSerializations();
}
