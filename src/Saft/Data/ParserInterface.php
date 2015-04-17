<?php
namespace Saft\Data;

/**
 * @note We have to decide how the ParserInterface should be implemented. One option
 * could be that a parser can accept multiple files/streams which are handled as one
 * graph and all statements are combined in the resulting StatementIterator.
 */
interface ParserInterface
{
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
     * @return \Saft\Rdf\StatementIterator a StatementIterator containing all the Statements parsed by the parser to far
     */
    public function parseStreamToIterator($inputStream, $baseUri = null, $serialization = null);

    /**
     * @unstable
     * @return array An associative array with a prefix mapping of the prefixes parsed so far. The key will be the
     *               prefix, while the values contains the according namespace URI.
     */
    public function getCurrentPrefixList();

    /**
     * @unstable
     * @return array of supported mimetypes which are understood by this parser
     */
    public function getSupportedSerializations();
}
