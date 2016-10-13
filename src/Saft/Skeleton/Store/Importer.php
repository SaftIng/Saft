<?php

namespace Saft\Skeleton\Store;

use Saft\Data\ParserSerializerUtils;
use Saft\Rdf\NamedNode;
use Saft\Skeleton\Data\ParserFactory;
use Saft\Store\Store;

/**
 * 
 */
class Importer
{
    /**
     * @var array of Parser
     */
    protected $parsers;

    /**
     * @var ParserFactory
     */
    protected $parserFactory;

    /**
     * @var Store
     */
    protected $store;

    /**
     * @param Store $store
     */
    public function __construct(Store $store, ParserFactory $parserFactory)
    {
        $this->store = $store;

        $this->parsers = array();

        // create suitable parser
        $this->parserFactory = $parserFactory;
    }

    /**
     * Don't use it anymore, will be removed soon.
     *
     * @deprecated
     */
    public function getFileSerialization($file)
    {
        return $this->getSerialization($file);
    }

    /**
     * @param string|resource $file
     * @return string
     * @throws \Exception if parameter $file is not of type string or resource.
     */
    public function getSerialization($target)
    {
        $format = null;
        $short = null;

        // filename given
        if (is_string($target) && file_exists($target)) {
            $short = file_get_contents($target);
            $format = \EasyRdf_Format::guessFormat($short);

        // string given
        } elseif (is_string($target)) {
            $short = $target;
            $format = \EasyRdf_Format::guessFormat($target);
        } else {
            throw new \Exception('Parameter $file must be the string itself or a filename.');
        }

        // TODO: hot patch, as long as https://github.com/njh/easyrdf/pull/272 is not merged.
        // in case you have a DOCTYPE element with many ENTITY elements, you should check
        // further parts of the file
        // first make sure that we have XML
        if (0 < preg_match('/\<\?xml/si', $short)) {
            // get the next portion of the data
            if (is_resource($target)) {
                $short = fread($target, 10000);
            } elseif (is_string($target)) {
                $short = substr($short, 1024, 10000);
            }
            // check again for <rdf:
            if (0 < preg_match('/<rdf:/i', $short)) {
                $format = 'rdfxml';
            }
        }

        if (null != $format) {
            // transform guessed EasyRdf format to Saft's format
            switch($format) {
                case 'json': return 'rdf-json';
                case 'ntriples': return 'n-triples';
                case 'rdfa': return 'rdfa';
                case 'rdfxml': return 'rdf-xml';
                case 'turtle': return 'turtle';
                default: return null;
            }
        }

        return $format;
    }

    /**
     * @param string $filepath
     * @param NamedNode $graph Graph to import data into.
     * @param true
     * @throws \Exception if parameter $file is not of type string or resource.
     * @throws \Exception if a non-n-triples file is to import.
     * @unstable
     */
    public function importFile($filename, NamedNode $graph)
    {
        $content = file_get_contents($filename);
        $serialization = $this->getSerialization($content);
        if (null == $serialization) {
            throw new \Exception('Your file/string has an unknown serialization: '. $serialization);
        }

        return $this->importString($content, $graph, $serialization);
    }

    /**
     * Imports a string assuming its serialized as n-triples.
     *
     * @param string $string
     * @param NamedNode $graph
     * @param string $serialization
     * @param true
     * @throws \Exception if parameter $graph is null.
     */
    public function importString($string, $graph, $serialization = 'n-triples')
    {
        if (in_array($serialization, $this->parserFactory->getSupportedSerializations())) {
            if (false == isset($this->parsers[$serialization])) {
                $this->parsers[$serialization] = $this->parserFactory->createParserFor($serialization);
            }
        } else {
            throw new \Exception('Given serialization is unknown: '. $serialization);
        }

        // parse string
        $iterator = $this->parsers[$serialization]->parseStringToIterator($string);

        // import its statements into the store
        $this->store->addStatements($iterator, $graph);

        return true;
    }
}
