<?php

namespace Saft\Rdf;

/**
 * Helper class which provides useful methods for Node related operations, for instance node creation or URI
 * checks.
 *
 * @api
 * @package Saft\Rdf
 * @since 0.1
 * @deprecated Use Saft/Rdf/RdfHelpers!
 */
class NodeUtils
{
    protected $rdfHelpers;

    public function __construct()
    {
        $this->rdfHelpers = new RdfHelpers();
    }

    /**
     * @param string $s
     * @return string encoded string for n-quads
     * @deprecated Use Saft/Rdf/RdfHelpers!
     */
    public function encodeStringLitralForNQuads($s)
    {
        return $this->rdfHelpers->encodeStringLitralForNQuads($s);
    }

    /**
     * Returns the regex string to get a node from a triple/quad.
     *
     * @param boolean $useVariables optional, default is false
     * @param boolean $useNamespacedUri optional, default is false
     * @return string
     * @deprecated Use Saft/Rdf/RdfHelpers!
     */
    public function getRegexStringForNodeRecognition(
        $useBlankNode = false,
        $useNamespacedUri = false,
        $useTypedString = false,
        $useLanguagedString = false,
        $useSimpleString = false,
        $useSimpleNumber = false,
        $useVariables = false
    ) {
        return $this->rdfHelpers->getRegexStringForNodeRecognition(
            $useBlankNode,
            $useNamespacedUri,
            $useTypedString,
            $useLanguagedString,
            $useSimpleString,
            $useSimpleNumber,
            $useVariables
        );
    }

    /**
     * @param string $stringToCheck
     * @return null|string
     * @deprecated Use Saft/Rdf/RdfHelpers!
     */
    public function guessFormat($stringToCheck)
    {
        return $this->rdfHelpers->guessFormat($stringToCheck);
    }

    /**
     * Checks if a given string is a blank node ID. Blank nodes are usually structured like
     * _:foo, whereas _: comes first always.
     *
     * @param string $string String to check if its a blank node ID or not.
     * @return boolean True if given string is a valid blank node ID, false otherwise.
     * @deprecated Use Saft/Rdf/RdfHelpers!
     */
    public function simpleCheckBlankNodeId($string)
    {
        return $this->rdfHelpers->simpleCheckBlankNodeId($string);
    }

    /**
     * Checks the general syntax of a given URI. Protocol-specific syntaxes are not checked. Instead, only
     * characters disallowed an all URIs lead to a rejection of the check. Use this function, if you need a
     * basic check and if performance is an issuse. In case you need a more precise check, that function is
     * not recommended.
     *
     * @param string $string String to check if its a URI or not.
     * @return boolean True if given string is a valid URI, false otherwise.
     * @api
     * @since 0.1
     * @deprecated Use Saft/Rdf/RdfHelpers!
     */
    public function simpleCheckURI($string)
    {
        return $this->rdfHelpers->simpleCheckURI($string);
    }
}
