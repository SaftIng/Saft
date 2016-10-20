<?php

namespace Saft\Rdf;

/**
 * Helper class which provides useful methods for Node related operations, for instance node creation or URI
 * checks.
 *
 * @api
 * @package Saft\Rdf
 * @since 0.1
 */
class NodeUtils
{
    /**
     * @param string $s
     * @return string encoded string for n-quads
     */
    public function encodeStringLitralForNQuads($s)
    {
        $s = str_replace('\\', '\\\\', $s);
        $s = str_replace("\t", '\t', $s);
        $s = str_replace("\n", '\n', $s);
        $s = str_replace("\r", '\r', $s);
        $s = str_replace('"', '\"', $s);

        return $s;
    }

    /**
     * Returns the regex string to get a node from a triple/quad.
     *
     * @param boolean $useVariables optional, default is false
     * @param boolean $useNamespacedUri optional, default is false
     * @return string
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
        $regex = '(<([a-z]{2,}:[^\s]*)>)'; // e.g. <http://foobar/a>

        if (true == $useBlankNode) {
            $regex .= '|(_:([a-z0-9A-Z_]+))'; // e.g. _:foobar
        }

        if (true == $useNamespacedUri) {
            $regex .= '|(([a-z0-9]+)\:([a-z0-9]+))'; // e.g. rdfs:label
        }

        if (true == $useTypedString) {
            // e.g. "Foo"^^<http://www.w3.org/2001/XMLSchema#string>
            $regex .= '|(\"(.*?)\"\^\^\<([^\s]+)\>)';
        }

        if (true == $useLanguagedString) {
            $regex .= '|(\"(.*?)\"\@([a-z\-]{2,}))'; // e.g. "Foo"@en
        }

        if (true == $useSimpleString) {
            $regex .= '|(\"(.*?)\")'; // e.g. "Foo"
        }

        if (true == $useSimpleNumber) {
            $regex .= '|([0-9]{1,})'; // e.g. 42
        }

        if (true == $useVariables) {
            $regex .= '|(\?[a-z0-9\_]+)'; // e.g. ?s
        }

        return $regex;
    }

    /**
     * @param string $stringToCheck
     * @return null|string
     */
    public function guessFormat($stringToCheck)
    {
        if (false == is_string($stringToCheck)) {
            throw new \Exception('Invalid $stringToCheck value given. It needs to be a string.');
        }

        $short = substr($stringToCheck, 0, 1024);

        // n-triples/n-quads
        if (0 < preg_match('/^<.+>\t*\s*<.+>/m', $short, $matches)) {
            return 'n-triples';
        // RDF/XML
        } elseif (0 < preg_match('/<rdf:/i', $short, $matches)) {
            return 'rdf-xml';
        }

        return null;
    }

    /**
     * Checks if a given string is a blank node ID. Blank nodes are usually structured like
     * _:foo, whereas _: comes first always.
     *
     * @param string $string String to check if its a blank node ID or not.
     * @return boolean True if given string is a valid blank node ID, false otherwise.
     */
    public function simpleCheckBlankNodeId($string)
    {
        return '_:' == substr($string, 0, 2);
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
     */
    public function simpleCheckURI($string)
    {
        $regEx = '/^([a-z]{2,}:[^\s]*)$/';
        return (1 === preg_match($regEx, (string)$string));
    }
}
