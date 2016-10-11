<?php

namespace Saft\Data;

use Sabre\Xml\Service;
use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\LiteralImpl;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\NodeFactory;
use Saft\Rdf\NodeUtils;
use Saft\Rdf\StatementImpl;
use Saft\Rdf\StatementFactory;

/**
 *
 */
class RDFXMLParser implements Parser
{
    protected $nodeFactory;

    protected $nodeUtils;

    protected $statementFactory;

    /**
     * @param NodeFactory $nodeFactory
     * @param StatementFactory $statementFactory
     * @param NodeUtils $nodeUtils
     */
    public function __construct(NodeFactory $nodeFactory, StatementFactory $statementFactory, NodeUtils $nodeUtils)
    {
        $this->nodeFactory = $nodeFactory;
        $this->nodeUtils = $nodeUtils;
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
    public function parseStringToIterator($xmlString, $baseUri = null)
    {
        // check $baseUri
        if (null !== $baseUri && false == $this->nodeUtils->simpleCheckURI($baseUri)) {
            throw new \Exception('No base URI support for now. To continue, just leave $baseUri = null.');
        }

        $service = new Service();
        $xmlArray = $service->parse($xmlString);

        $rdfAboutString = '{http://www.w3.org/1999/02/22-rdf-syntax-ns#}about';
        $rdfDatatypeString = '{http://www.w3.org/1999/02/22-rdf-syntax-ns#}datatype';
        $rdfDescriptionString = '{http://www.w3.org/1999/02/22-rdf-syntax-ns#}Description';
        $rdfResourceString = '{http://www.w3.org/1999/02/22-rdf-syntax-ns#}resource';
        $xmlNamespaceLangString = '{http://www.w3.org/XML/1998/namespace}lang';

        $statements = array();

        // go through all rdf:Description elements
        foreach ($xmlArray as $rdfDescription) {
            // if its a rdf:Description element
            if ($rdfDescription['name'] == $rdfDescriptionString) {
                // create subject
                $subject = $this->nodeFactory->createNamedNode(
                    $rdfDescription['attributes'][$rdfAboutString]
                );

                foreach ($rdfDescription['value'] as $value) {

                    // if object is a resource
                    if (isset($value['attributes'][$rdfResourceString]) && $value['attributes'][$rdfResourceString]) {
                        // create predicate
                        $predicate = $this->nodeFactory->createNamedNode(
                            str_replace(array('{', '}'), '', $value['name'])
                        );

                        // we know that the object can only be a named node, so add triple to statements and go the
                        // next entry
                        $statements[] = $this->statementFactory->createStatement(
                            $subject,
                            $predicate,
                            $this->nodeFactory->createNamedNode($value['attributes'][$rdfResourceString])
                        );

                        continue;

                    // at least one custom predicate is used
                    } elseif (isset($rdfDescription['attributes'][$rdfAboutString])
                        && $rdfDescription['attributes'][$rdfAboutString]) {
                        foreach ($rdfDescription['value'] as $objectValue) {
                            // var_dump($objectValue['attributes']);

                            $predicate = $this->nodeFactory->createNamedNode(
                                str_replace(array('{', '}'), '', $objectValue['name'])
                            );

                            // object is URI
                            if (isset($objectValue['attributes'][$rdfResourceString])
                                && $this->nodeUtils->simpleCheckURI($objectValue['attributes'][$rdfResourceString])) {
                                $object = $this->nodeFactory->createNamedNode($objectValue['attributes'][$rdfResourceString]);

                            // object is blank node
                            } elseif ($this->nodeUtils->simpleCheckBlankNodeId($objectValue['value'])) {
                                $object = $this->nodeFactory->createBlankNode($objectValue['value']);

                            // guess object is of type literal
                            } else {

                                // check for language
                                if (isset($objectValue['attributes'][$xmlNamespaceLangString])) {
                                    $lang = $objectValue['attributes'][$xmlNamespaceLangString];
                                    $datatype = null;

                                // check for datatype
                                } elseif (isset($objectValue['attributes'][$rdfDatatypeString])) {
                                    $lang = null;
                                    $datatype = $objectValue['attributes'][$rdfDatatypeString];
                                }

                                $object = $this->nodeFactory->createLiteral(
                                    str_replace(array('{', '}'), '', $objectValue['value']),
                                    $datatype,
                                    $lang
                                );
                            }

                            $statements[] = $this->statementFactory->createStatement($subject, $predicate, $object);
                            continue;
                        }
                    }
                }
            }
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
}
