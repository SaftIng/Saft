<?php

namespace Saft\Data;

interface ParserFactory
{
    /**
     * Creates a Parser instance for a given format, if available.
     *
     * @param  string     $serialization The serialization you need a parser for. In case it is not
     *                                   available, an exception will be thrown.
     * @return Parser     Suitable parser for the requested format.
     * @throws \Exception If parser for requested format is not available.
     */
    public function createParserFor($serialization);

    /**
     * Returns an array which contains supported serializations.
     *
     * @return array Array of supported serializations which are understood by this parser.
     */
    public function getSupportedSerializations();
}
