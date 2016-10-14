<?php

namespace Saft\Data;

/**
 * Helper class for parser and serializer tasks.
 *
 * @package Saft\Data
 */
class ParserSerializerUtils
{
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
    ){
        $regex = '(\<([a-zA-Z0-9\.\/\:#\-_]+)\>'; // e.g. <http://foobar/a>

        if (true == $useTypedString) {
            // e.g. "Foo"^^<http://www.w3.org/2001/XMLSchema#string>
            $regex .= '|\"(.*?)\"\^\^\<([a-z0-9\.\/\:#-_+?=%]+)\>';
        }

        if (true == $useLanguagedString) {
            $regex .= '|\"(.*?)\"\@([a-z\-]{2,})'; // e.g. "Foo"@en
        }

        if (true == $useSimpleString) {
            $regex .= '|\"(.*?)\"[\s|\.|\n|\}]*?'; // e.g. "Foo"
        }

        if (true == $useSimpleNumber) {
            $regex .= '|([0-9]{1,})'; // e.g. 42
        }

        if (true == $useVariables) {
            $regex .= '|(\?[a-z0-9\_]+)'; // e.g. ?s
        }

        if (true == $useNamespacedUri) {
            $regex .= '|([a-z0-9]+)\:([a-z0-9]+)'; // e.g. rdfs:label
        }

        if (true == $useBlankNode) {
            $regex .= '|_:([a-z0-9A-Z_]+)'; // e.g. _:foobar
        }

        $regex .= ')';
        return $regex;
    }
}
