<?php

namespace Saft\Skeleton\Store;

use Saft\Rdf\NamedNode;
use Saft\Rdf\NodeFactory;
use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\StatementFactoryImpl;
use Saft\Skeleton\Data\ParserFactory;
use Saft\Store\Store;

/**
 * Please use Importer instead of FileImporter. Class will be removed in future releases.
 *
 * @deprecated
 */
class FileImporter
{
    /**
     * @var Parser
     */
    protected $parser;

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
    public function __construct(Store $store)
    {
        $this->store = $store;

        // create suitable parser
        $this->parserFactory = new ParserFactory(new NodeFactoryImpl(), new StatementFactoryImpl());
        $this->parser = $this->parserFactory->createParserFor('n-triples');
    }

    /**
     * @param string|resource $file
     * @throws \Exception if parameter $file is not of type string or resource.
     */
    public function getFileSerialization($file)
    {
        $format = null;

        if (is_resource($file)) {
            $format = \EasyRdf_Format::guessFormat(fread($file, 1024));
            // set file pointer to position 0;
            rewind($file);
        } elseif (is_string($file)) {
            $format = \EasyRdf_Format::guessFormat('', $file);
        } else {
            throw new \Exception('Parameter $file must be of type string or resource.');
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
     * @param string|resource $file
     * @param NamedNode $graph Graph to import data into.
     * @param int $containerSize Number of lines you want to read before importing them.
     * @throws \Exception if parameter $file is not of type string or resource.
     * @throws \Exception if a non-n-triples file is to import.
     * @unstable
     */
    public function importFile($file, NamedNode $graph, $containerSize = 10)
    {
        $locallyOpened = false;
        $containerSize = (int)$containerSize;

        if ($containerSize < 1) {
            $containerSize = 10;
        }

        if (is_resource($file)) {
            // OK
        } elseif (is_string($file)) {
            $file = fopen($file, 'r');
            // relevant later on. we close only locally opened file references after import is done.
            $locallyOpened = true;
        } else {
            throw new \Exception('Parameter $file must be of type string or resource.');
        }

        $fileSerialization = $this->getFileSerialization($file);

        if ('n-triples' !== $fileSerialization) {
            throw new \Exception('Currently only n-triple files can be imported. Yours is: '. $fileSerialization);
        }

        try {
            $collectedLineNumber = 0;
            $collectedLines = '';
            $importedLines = 0;

            // iterator abouot lines of the file
            while (!feof($file)) {
                ++$importedLines;
                $collectedLines .= fgets($file);

                // after the threshold was reached, import collected lines
                // and reset line counter
                if (++$collectedLineNumber == $containerSize) {
                    $this->importString($collectedLines, $graph);
                    $collectedLines = '';
                    $collectedLineNumber = 0;
                }
            }

            // import remaining lines also
            $this->importString($collectedLines, $graph);

            if ($locallyOpened) {
                fclose ($file);
            }

            return $importedLines;

        } catch(\Exception $e) {
            // if we created a file reference, close it, before reporting the exception
            if ($locallyOpened) {
                fclose ($file);
            }
            throw $e;
        }
    }

    /**
     * Imports a string assuming its serialized as n-triples.
     *
     * @param string $string
     * @param NamedNode $graph
     * @throws \Exception if parameter $graph is null.
     */
    public function importString($string, $graph)
    {
        // parse string
        $iterator = $this->parser->parseStringToIterator($string);

        // import its statements into the store
        $this->store->addStatements($iterator, $graph);
    }
}
