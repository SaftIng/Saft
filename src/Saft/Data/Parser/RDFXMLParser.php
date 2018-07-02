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

namespace Saft\Data\Parser;

use Saft\Data\Parser;
use Sabre\Xml\Service;
use Saft\Rdf\NodeFactory;
use Saft\Rdf\RdfHelpers;
use Saft\Rdf\StatementFactory;
use Saft\Rdf\StatementIterator;
use Saft\Rdf\StatementIteratorFactory;

/**
 * A handy RDF/XML parser.
 */
class RDFXMLParser implements Parser
{
    protected $nodeFactory;

    protected $rdfHelpers;

    protected $statementFactory;

    protected $statementIteratorFactory;

    /**
     * @param NodeFactory              $nodeFactory
     * @param StatementFactory         $statementFactory
     * @param StatementIteratorFactory $statementIteratorFactory
     * @param RdfHelpers               $rdfHelpers
     */
    public function __construct(
        NodeFactory $nodeFactory,
        StatementFactory $statementFactory,
        StatementIteratorFactory $statementIteratorFactory,
        RdfHelpers $rdfHelpers
    ) {
        $this->nodeFactory = $nodeFactory;
        $this->rdfHelpers = $rdfHelpers;
        $this->statementFactory = $statementFactory;
        $this->statementIteratorFactory = $statementIteratorFactory;
    }

    /**
     * Returns an array of prefixes which where found during the last parsing.
     *
     * @return array An associative array with a prefix mapping of the prefixes parsed so far. The key
     *               will be the prefix, while the values contains the according namespace URI.
     */
    public function getCurrentPrefixList(): array
    {
        // TODO implement a way to get a list of all namespaces used in the last parsed datastring/file.
        throw new \Exception('Not implemented yet.');
    }

    /**
     * Parses a given string and returns an iterator containing Statement instances representing the read data.
     *
     * @param string $inputString data string containing RDF serialized data
     * @param string $baseUri     The base URI of the parsed content. If this URI is null the inputStreams URL
     *                            is taken as base URI.
     *
     * @return StatementIterator StatementIterator instaince containing all the Statements parsed by the
     *                           parser to far
     *
     * @throws \Exception if the base URI $baseUri is no valid URI
     */
    public function parseStringToIterator(string $inputString, string $baseUri = null): StatementIterator
    {
        // check $baseUri
        if (null !== $baseUri) {
            throw new \Exception('No base URI support for now. To continue, just leave $baseUri = null.');
        }

        $service = new Service();
        $xmlArray = $service->parse($inputString);

        $rdfAboutString = '{http://www.w3.org/1999/02/22-rdf-syntax-ns#}about';
        $rdfDatatypeString = '{http://www.w3.org/1999/02/22-rdf-syntax-ns#}datatype';
        $rdfDescriptionString = '{http://www.w3.org/1999/02/22-rdf-syntax-ns#}Description';
        $nodeIDString = '{http://www.w3.org/1999/02/22-rdf-syntax-ns#}nodeID';
        $rdfResourceString = '{http://www.w3.org/1999/02/22-rdf-syntax-ns#}resource';
        $xmlNamespaceLangString = '{http://www.w3.org/XML/1998/namespace}lang';

        $statements = [];

        // go through all rdf:Description elements
        foreach ($xmlArray as $rdfDescription) {
            // if its a rdf:Description element
            if ($rdfDescription['name'] == $rdfDescriptionString) {
                /*
                 * create subject
                 */
                // subject is a named node
                if (isset($rdfDescription['attributes'][$rdfAboutString])) {
                    $subject = $this->nodeFactory->createNamedNode(
                        $rdfDescription['attributes'][$rdfAboutString]
                    );

                // subject is a blank node
                } elseif (isset($rdfDescription['attributes'][$nodeIDString])) {
                    $subject = $this->nodeFactory->createBlankNode(
                        $rdfDescription['attributes'][$nodeIDString]
                    );
                }

                foreach ($rdfDescription['value'] as $value) {
                    // if object is a resource
                    if (isset($value['attributes'][$rdfResourceString])
                        && $value['attributes'][$rdfResourceString]) {
                        // create predicate
                        $predicate = $this->nodeFactory->createNamedNode(
                            \str_replace(['{', '}'], '', $value['name'])
                        );

                        // we know that the object can only be a named node, so add triple
                        // to statements and go the next entry
                        $statements[] = $this->statementFactory->createStatement(
                            $subject,
                            $predicate,
                            $this->nodeFactory->createNamedNode(
                                $value['attributes'][$rdfResourceString]
                            )
                        );

                        continue;

                    // at least one custom predicate is used
                    } elseif (isset($rdfDescription['attributes'][$rdfAboutString])
                        && $rdfDescription['attributes'][$rdfAboutString]) {
                        foreach ($rdfDescription['value'] as $objectValue) {
                            $predicate = $this->nodeFactory->createNamedNode(
                                \str_replace(['{', '}'], '', $objectValue['name'])
                            );

                            // object is URI
                            if (isset($objectValue['attributes'][$rdfResourceString])
                                && $this->rdfHelpers->simpleCheckURI(
                                    $objectValue['attributes'][$rdfResourceString])
                                ) {
                                $object = $this->nodeFactory->createNamedNode(
                                    $objectValue['attributes'][$rdfResourceString]
                                );

                            // object is blank node
                            } elseif (null == $objectValue['value']
                                && isset($objectValue['attributes'][$nodeIDString])) {
                                $object = $this->nodeFactory->createBlankNode($objectValue['attributes'][$nodeIDString]);

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
                                    \str_replace(['{', '}'], '', $objectValue['value']),
                                    $datatype,
                                    $lang
                                );
                            }

                            $statements[] = $this->statementFactory->createStatement(
                                $subject,
                                $predicate,
                                $object
                            );
                            continue;
                        }
                    }
                }
            }
        }

        return $this->statementIteratorFactory->createStatementIteratorFromArray($statements);
    }

    /**
     * Parses a given stream and returns an iterator containing Statement instances
     * representing the previously read data. The stream parses the data not as a whole but
     * in chunks.
     *
     * @param string $inputStream filename of the stream to parse which contains RDF
     *                            serialized data
     * @param string $baseUri     The base URI of the parsed content. If this URI is null,
     *                            the inputStreams URL is taken as base URI. (optional)
     *
     * @return StatementIterator a StatementIterator containing all the Statements parsed by
     *                           the parser to far
     *
     * @throws \Exception if the base URI $baseUri is no valid URI
     *
     * @api
     *
     * @since 2.0.0
     */
    public function parseStreamToIterator(string $inputStream, string $baseUri = null): StatementIterator
    {
        return $this->parseStringToIterator(\file_get_contents($inputStream), $baseUri);
    }
}
