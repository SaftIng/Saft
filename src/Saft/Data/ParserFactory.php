<?php

namespace Saft\Data;

interface ParserFactory
{
    /**
     * Creates a Parser instance for a given format, if available.
     *
     * @param  string     $format The format you need a parser for. In case it is not available, an exception
     *                            will be thrown.
     * @return Parser     Suitable parser for the requested format.
     * @throws \Exception If parser for requested format is not available.
     */
    public function createParserFor($format);
}
