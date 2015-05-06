<?php

namespace Saft\Backend\EasyRdf\Data;

use EasyRdf_Format;
use EasyRdf_Graph;
use Saft\Backend\EasyRdf\Data\AbstractParser;
use Saft\Data\Parser;
use Saft\Rdf\ArrayStatementIteratorImpl;

class StringParser extends AbstractParser
{
    /**
     * Parses a given string and builds a StatementIterator.
     *
     * @param  string            $inputString
     * @return StatementIterator
     */
    public function parseStringToIterator($inputString, $baseUri = null, $serialization = null)
    {
        $graph = new EasyRdf_Graph();

        if ($serialization === null) {
            $format = EasyRdf_Format::guessFormat($inputString);
        } else {
            // TODO implement creation of format
            $format = null;
        }
        $graph->parse($inputString, $format->getName());

        // transform parsed data to PHP array
        return $this->rdfPhpToStatementIterator($graph->toRdfPhp());
    }


    /**
     * Maybe the PHP Stream API will be relevant here:
     * http://php.net/manual/en/book.stream.php
     * @unstable
     * @param $inputStream string filename of the stream to parse {@url http://php.net/manual/en/book.stream.php}
     * @param $baseUri string the base URI of the parsed content. If this URI is null the inputStreams URL is taken as
     *                        base URI. (If the base URI is no valid URI an Exception will be thrown.)
     * @param $serialization string the serialization of the inputStream. If null is given the parser will either apply
     *                              some standard serialization, orw the only one it is supporting, or will try to guess
     *                              the correct serialization, or will throw an Exception.
     * @return StatementIterator a StatementIterator containing all the Statements parsed by the parser to far
     */
    public function parseStreamToIterator($inputStream, $baseUri = null, $serialization = null)
    {
        // TODO
    }

    /**
     * @unstable
     * @return array An associative array with a prefix mapping of the prefixes parsed so far. The key will be the
     *               prefix, while the values contains the according namespace URI.
     */
    public function getCurrentPrefixList()
    {
        // TODO
    }

    /**
     * @unstable
     * @return array of supported mimetypes which are understood by this parser
     */
    public function getSupportedSerializations()
    {
        // TODO
    }
}
