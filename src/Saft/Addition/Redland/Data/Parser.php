<?php

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
     * @param $inputString
     * @param $baseUri
     * @return StatementIterator
     * @throws Exception
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
     * @param $inputStream
     * @param $baseUri
     * @return StatementIterator
     * @throws Exception
     */
    public function parseStreamToIterator($inputStream, $baseUri = null)
    {
        $rdfUri = librdf_new_uri($this->world, $baseUri);

        if (false === $rdfUri) {
            throw new \Exception('Failed to create librdf_uri from: '. $baseUri);
        }

        $data = file_get_contents($inputStream);

        return $this->parseStringToIterator($data, $baseUri, $serialization);
    }

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
