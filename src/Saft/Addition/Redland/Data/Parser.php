<?php

/*
 * This file is part of Saft.
 *
 * (c) Konrad Abicht <hi@inspirito.de>
 * (c) Natanael Arndt <arndt@informatik.uni-leipzig.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Saft\Addition\Redland\Data;

use Saft\Data\Parser as ParserInterface;
use Saft\Addition\Redland\Rdf\StatementIterator;

class Parser implements ParserInterface
{
    /**
     * The redland world instance
     */
    protected $world;

    /**
     * The prefixes seen so far while parsing the input
     * @var array keys are prefixes, values are the uris
     */
    protected $prefixes = array();

    /**
     * This instance of the redland parser
     * @var librdf_parser
     */
    protected $parser;

    /**
     * Constructor.
     *
     * @param string $serialization Serialization term to use.
     */
    public function __construct($serialization)
    {
        if (!extension_loaded('redland')) {
            throw new \Exception('Redland php5-librdf is required for this parser');
        }

        $this->world = librdf_php_get_world();
        $this->parser = librdf_new_parser($this->world, $serialization, null, null);

        if (false === $this->parser || null === $this->parser) {
            throw new \Exception('Failed to create librdf_parser of type: ' . $serialization);
        }
    }

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
    public function parseStringToIterator($inputString, $baseUri = null)
    {
        $redlandStream = librdf_parser_parse_string_as_stream($this->parser, $inputString, $baseUri);
        if (false === $redlandStream || null === $redlandStream) {
            throw new \Exception('Failed to parse RDF stream');
        }

        return new StatementIterator($redlandStream);
    }

    /**
     * Parses a given stream and returns an iterator containing Statement instances representing the
     * previously read data. The stream parses the data not as a whole but in chunks.
     *
     * @param string $inputStream Filename of the stream to parse which contains RDF serialized data.
     * @param string $baseUri     The base URI of the parsed content. If this URI is null, the inputStreams URL is taken
     *                            as base URI. (optional)
     * @return StatementIterator A StatementIterator containing all the Statements parsed by the parser to far.
     * @throws \Exception if creation of librdf_uri from the given $baseUri failed.
     */
    public function parseStreamToIterator($inputStream, $baseUri = null)
    {
        $rdfUri = librdf_new_uri($this->world, $baseUri);

        if (false === $rdfUri) {
            throw new \Exception('Failed to create librdf_uri from: '. $baseUri);
        }

        $data = file_get_contents($inputStream);

        return $this->parseStringToIterator($data, $baseUri);
    }

    /**
     * Returns an array of prefixes which where found during the last parsing. Might also be any other prefix list
     * depending on the implementation. Might even be empty.
     *
     * @return array An associative array with a prefix mapping of the prefixes parsed so far. The key
     *               will be the prefix, while the values contains the according namespace URI.
     */
    public function getCurrentPrefixlist()
    {
        $prefixCount = count($this->prefixes);
        $parserPrefixCount = librdf_parser_get_namespaces_seen_count($this->parser);

        /*
         * Asumption, that redland internaly keeps a distinct list of prefixes
         */
        if ($prefixCount < $parserPrefixCount) {
            for ($i = $prefixCount; $i < $parserPrefixCount; ++$i) {
                $prefix = librdf_parser_get_namespaces_seen_prefix($this->parser, $i);
                $uri = librdf_parser_get_namespaces_seen_uri($this->parser, $i);
                $this->prefixes[$prefix] = librdf_uri_as_string($uri);
            }
        }
        return $this->prefixes;
    }

    /**
     * @unstable
     * @return array of supported mimetypes which are understood by this parser
     */
    public function getSupportedSerializations()
    {
        return array();
    }
}
