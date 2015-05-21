<?php

namespace Saft\Data;

/**
 * @note We have to decide how the ParserInterface should be implemented. One option
 * could be that a parser can accept multiple files/streams which are handled as one
 * graph and all statements are combined in the resulting StatementIterator.
 */
interface Parser
{
    /**
     * Parses a given string and returns an iterator containing Statement instances representing the
     * previously read data.
     *
     * @param  string            $inputString   Data string containing RDF serialized data.
     * @param  string            $baseUri       The base URI of the parsed content. If this URI is null the
     *                                          inputStreams URL is taken as base URI.
     * @param  string            $serialization The serialization of the inputStream. If null is given the
     *                                          parser will either apply some standard serialization, or the
     *                                          only one it is supporting, or will try to guess the correct
     *                                          serialization, or will throw an Exception.
     *                                          Supported formats are a subset of the following:
     *                                          json, rdfxml, sparql-xml, rdfa, turtle, ntriples, n3
     * @return StatementIterator StatementIterator instaince containing all the Statements parsed by the
     *                           parser to far
     * @throws \Exception        If the base URI $baseUri is no valid URI.
     */
    public function parseStringToIterator($inputString, $baseUri = null, $serialization = null);

    /**
     * Parses a given stream and returns an iterator containing Statement instances representing the
     * previously read data. The stream parses the data not as a whole but in chunks.
     *
     * @param  string            $inputStream   Filename of the stream to parse which contains RDF serialized
     *                                          data.
     * @param  string            $baseUri       The base URI of the parsed content. If this URI is null
     *                                          the inputStreams URL is taken as base URI.
     * @param  string            $serialization The serialization of the inputStream. If null is given the
     *                                          parser will either apply some standard serialization, or the
     *                                          only one it is supporting, or will try to guess the correct
     *                                          serialization, or will throw an Exception.
     *                                          Supported formats are a subset of the following:
     *                                          json, rdfxml, sparql-xml, rdfa, turtle, ntriples, n3
     * @return StatementIterator A StatementIterator containing all the Statements parsed by the parser to
     *                           far.
     * @throws \Exception        If the base URI $baseUri is no valid URI.
     */
    public function parseStreamToIterator($inputStream, $baseUri = null, $serialization = null);

    /**
     * Returns an array of prefixes which where found during the last parsing.
     *
     * @return array An associative array with a prefix mapping of the prefixes parsed so far. The key
     *               will be the prefix, while the values contains the according namespace URI.
     */
    public function getCurrentPrefixList();

    /**
     * Returns an array which contains supported serializations.
     *
     * @return array Array of supported serializations which are understood by this parser.
     */
    public function getSupportedSerializations();
}
