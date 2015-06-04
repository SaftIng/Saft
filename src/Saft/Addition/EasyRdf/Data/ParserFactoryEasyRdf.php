<?php

namespace Saft\Addition\EasyRdf\Data;

use Saft\Data\ParserFactory;
use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\StatementFactoryImpl;

class ParserFactoryEasyRdf implements ParserFactory
{
    /**
     * Creates a Parser instance for a given serialization, if available.
     *
     * @param  string     $serialization The serialization you need a parser for. In case it is not
     *                                   available, an exception will be thrown.
     * @return Parser     Suitable parser for the requested serialization.
     * @throws \Exception If parser for requested serialization is not available.
     */
    public function createParserFor($serialization)
    {
        $parser = new ParserEasyRdf(new NodeFactoryImpl(), new StatementFactoryImpl());

        if (in_array($serialization, $parser->getSupportedSerializations())) {
            return $parser;

        } else {
            throw new \Exception(
                'Requested serialization '. $serialization .' is not available in: '.
                implode(', ', $parser->getSupportedSerializations())
            );
        }
    }
}
