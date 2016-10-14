<?php

namespace Saft\Data;

use Sabre\Xml\Service;
use Saft\Data\ParserSerializerUtils;
use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\LiteralImpl;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\NodeFactory;
use Saft\Rdf\NodeUtils;
use Saft\Rdf\StatementImpl;
use Saft\Rdf\StatementFactory;

/**
 * Parser for n-triples and n-quads (RDF).
 */
class NQuadsParser implements Parser
{
    protected $nodeFactory;

    protected $nodeUtils;

    protected $parserSerializerUtils;

    protected $statementFactory;

    /**
     * @param NodeFactory $nodeFactory
     * @param StatementFactory $statementFactory
     * @param NodeUtils $nodeUtils
     * @param ParserSerializerUtils $parserSerializerUtils
     */
    public function __construct(
        NodeFactory $nodeFactory,
        StatementFactory $statementFactory,
        NodeUtils $nodeUtils,
        ParserSerializerUtils $parserSerializerUtils
    ) {
        $this->nodeFactory = $nodeFactory;
        $this->nodeUtils = $nodeUtils;
        $this->parserSerializerUtils = $parserSerializerUtils;
        $this->statementFactory = $statementFactory;
    }

    /**
     * Returns an array of prefixes which where found during the last parsing.
     *
     * @return array An associative array with a prefix mapping of the prefixes parsed so far. The key
     *               will be the prefix, while the values contains the according namespace URI.
     */
    public function getCurrentPrefixList()
    {
        // TODO implement a way to get a list of all namespaces used in the last parsed datastring/file.
        return array();
    }

    /**
     * Parses a given string and returns an iterator containing Statement instances representing the read data.
     *
     * @param  string $inputString Data string containing RDF serialized data.
     * @param  string $baseUri     The base URI of the parsed content. If this URI is null the inputStreams URL
     *                             is taken as base URI.
     * @return StatementIterator StatementIterator instaince containing all the Statements parsed by the
     *                           parser to far
     * @throws \Exception if the base URI $baseUri is no valid URI.
     */
    public function parseStringToIterator($inputString, $baseUri = null)
    {
        // check $baseUri
        if (null !== $baseUri && false == $this->nodeUtils->simpleCheckURI($baseUri)) {
            throw new \Exception('No base URI support for now. To continue, just leave $baseUri = null.');
        }

        $statements = array();

        preg_match_all(
            '/' .
            $this->parserSerializerUtils->getRegexStringForNodeRecognition(true) .'\s*\t*'.
            $this->parserSerializerUtils->getRegexStringForNodeRecognition() .'\s*\t*'.
            $this->parserSerializerUtils->getRegexStringForNodeRecognition(true, true, true, true) .
            '/im',
            $inputString,
            $matches
        );

        $matchCount = count($matches[0]);
        for ($i = 0; $i < $matchCount; ++$i) {
            $statements[] = $this->statementFactory->createStatement(
                $this->nodeUtils->createNodeInstanceFromString($matches[1][$i]),
                $this->nodeUtils->createNodeInstanceFromString($matches[4][$i]),
                $this->nodeUtils->createNodeInstanceFromString($this->unescapeString($matches[6][$i]))
            );
        }

        return new ArrayStatementIteratorImpl($statements);
    }

    /**
     * Parses a given stream and returns an iterator containing Statement instances representing the
     * previously read data. The stream parses the data not as a whole but in chunks.
     *
     * @param string $inputStream Filename of the stream to parse which contains RDF serialized data.
     * @param string $baseUri     The base URI of the parsed content. If this URI is null, the inputStreams URL is taken
     *                            as base URI. (optional)
     * @return StatementIterator A StatementIterator containing all the Statements parsed by the parser to far.
     * @throws \Exception if the base URI $baseUri is no valid URI.
     * @api
     * @since 0.1
     */
    public function parseStreamToIterator($inputStream, $baseUri = null)
    {
        return $this->parseStringToIterator(file_get_contents($inputStream), $baseUri);
    }

    /**
     * Decodes an encoded N-Triples string. Any \-escape sequences are substituted
     * with their decoded value.
     *
     * Copied from: https://github.com/njh/easyrdf/blob/master/lib/Parser/Ntriples.php
     *
     * @param  string $str An encoded N-Triples string.
     * @return string The unencoded string.
     **/
    protected function unescapeString($str)
    {
        if (strpos($str, '\\') === false) {
            return $str;
        }
        $mappings = array(
            't' => chr(0x09),
            'b' => chr(0x08),
            'n' => chr(0x0A),
            'r' => chr(0x0D),
            'f' => chr(0x0C),
            '\"' => chr(0x22),
            '\'' => chr(0x27)
        );
        foreach ($mappings as $in => $out) {
            $str = preg_replace('/\x5c([' . $in . '])/', $out, $str);
        }
        if (stripos($str, '\u') === false) {
            return $str;
        }
        while (preg_match('/\\\(U)([0-9A-F]{8})/', $str, $matches) ||
               preg_match('/\\\(u)([0-9A-F]{4})/', $str, $matches)) {
            $no = hexdec($matches[2]);
            if ($no < 128) {                // 0x80
                $char = chr($no);
            } elseif ($no < 2048) {         // 0x800
                $char = chr(($no >> 6) + 192) .
                        chr(($no & 63) + 128);
            } elseif ($no < 65536) {        // 0x10000
                $char = chr(($no >> 12) + 224) .
                        chr((($no >> 6) & 63) + 128) .
                        chr(($no & 63) + 128);
            } elseif ($no < 2097152) {      // 0x200000
                $char = chr(($no >> 18) + 240) .
                        chr((($no >> 12) & 63) + 128) .
                        chr((($no >> 6) & 63) + 128) .
                        chr(($no & 63) + 128);
            } else {
                # FIXME: throw an exception instead?
                $char = '';
            }
            $str = str_replace('\\' . $matches[1] . $matches[2], $char, $str);
        }
        return $str;
    }
}
