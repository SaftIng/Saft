<?php
namespace Saft\Backend\Redland\Data;

use \Saft\Data\Parser as ParserInterface;
use \Saft\Backend\Redland\Rdf\StatementIterator;

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

    public function __construct()
    {
        if (!extension_loaded('redland')) {
            throw new Exception('Redland php5-librdf is required for this parser');
        }
        // var_dump(get_extension_funcs('redland'));

        $format = 'turtle';

        $this->world = librdf_new_world();
        $this->parser = librdf_new_parser($this->world, $format, null, null);

        if (!$this->parser) {
            throw new Exception(
                "Failed to create librdf_parser of type: $format"
            );
        }
    }

    /**
     * @param $inputString
     * @param $baseUri
     * @param $serialization
     * @return StatementIterator
     * @throws Exception
     */
    public function parseStringToIterator($inputString, $baseUri = null, $serialization = null)
    {
        $redlandStream = librdf_parser_parse_string_as_stream($this->parser, $data, $rdfUri);
        if (!$redlandStream) {
            throw new Exception(
                "Failed to parse RDF stream"
            );
        }

        return new StatementIterator($redlandStream);
    }

    /**
     * @param $inputStream
     * @param $baseUri
     * @param $serialization
     * @return StatementIterator
     * @throws Exception
     */
    public function parseStreamToIterator($inputStream, $baseUri = null, $serialization = null)
    {
        $rdfUri = librdf_new_uri($this->world, $baseUri);
        if (!$rdfUri) {
            throw new Exception(
                "Failed to create librdf_uri from: $baseUri"
            );
        }

        $data = file_get_contents($inputStream);

        return $this->parseStingToIterator($data, $baseUri, $serialization);
    }

    public function getCurrentPrefixlist()
    {
        $prefixCount = count($this->prefixes);
        $parserPrefixCount = librdf_parser_get_namespaces_seen_count($this->parser);

        /*
         * Asumption, that redland internaly keeps a distinct list of prefixes
         */
        if ($prefixCount < $parserPrefixCount) {
            for ($i = $prefixCount; $i < $parserPrefixCount; $i++) {
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
        return [];
    }
}
