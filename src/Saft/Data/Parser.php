<?php

namespace Saft\Data;

/**
 * The Parser interface describes what methods a RDF parser should provide. An instance of Parser must be initialized
 * with a certain serialization the parser is able to parse. That means, that you have to create different instances
 * of Parser for each serialization you need.
 *
 * @api
 * @package Saft\Data
 */
interface Parser
{
    /**
     * Parses a given string and returns an iterator containing Statement instances representing the
     * previously read data.
     *
     * @param string $inputString Data string containing RDF serialized data.
     * @param string $baseUri     The base URI of the parsed content. If this URI is null the inputStreams URL
     *                            is taken as base URI.
     * @return StatementIterator StatementIterator instaince containing all the Statements parsed by the
     *                           parser to far.
     * @throws \Exception If the base URI $baseUri is no valid URI.
     */
    public function parseStringToIterator($inputString, $baseUri = null);

    /**
     * Parses a given stream and returns an iterator containing Statement instances representing the
     * previously read data. The stream parses the data not as a whole but in chunks.
     *
     * @param string $inputStream Filename of the stream to parse which contains RDF serialized data.
     * @param string $baseUri     The base URI of the parsed content. If this URI is null, the inputStreams URL is taken
     *                            as base URI. (optional)
     * @return StatementIterator A StatementIterator containing all the Statements parsed by the parser to far.
     * @throws \Exception if the base URI $baseUri is no valid URI.
     */
    public function parseStreamToIterator($inputStream, $baseUri = null);

    /**
     * Returns an array of prefixes which where found during the last parsing. Might also be any other prefix list
     * depending on the implementation. Might even be empty.
     *
     * @return array An associative array with a prefix mapping of the prefixes parsed so far. The key
     *               will be the prefix, while the values contains the according namespace URI.
     */
    public function getCurrentPrefixList();
}
