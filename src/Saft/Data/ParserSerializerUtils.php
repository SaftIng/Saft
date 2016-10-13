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
     * Returns the regex string to get the object part of a RDF triple/quad.
     *
     * @param boolean $ignoreVariables optional, default is false
     * @return string
     */
    public function getRegexStringForObjects($ignoreVariables = false)
    {
        $regex = '(' .
            '\<[a-zA-Z0-9\.\/\:#\-_]+\>|' .            // e.g. <http://foobar/a>
            '[a-z0-9]+\:[a-z0-9]+|' .                  // e.g. rdfs:label
            '\".*?\"\^\^\<[a-z0-9\.\/\:#-_+?=%]+\>|' . // e.g. "Foo"^^<http://www.w3.org/2001/XMLSchema#string>
            '\".*?\"\@[a-z\-]{2,}|' .                  // e.g. "Foo"@en
            '\".*?\"[\s|\.|\n|\}]*?|' .                // e.g. "Foo"
            '[0-9]{1,}';                               // e.g. 42

        if (false == $ignoreVariables) {
            $regex .= '|\?[a-z0-9\_]+'; // e.g. ?s
        }

        $regex .= ')';
        return $regex;
    }

    /**
     * Returns the regex string to get the predicate part of a RDF triple/quad.
     *
     * @param boolean $ignoreVariables optional, default is false
     * @return string
     */
    public function getRegexStringForPredicates($ignoreVariables = false)
    {
        $regex = '(' .
            '\<[a-z0-9\.\/\:#\-_]+\>|' . // e.g. <http://foobar/a>
            '[a-z0-9]+\:[a-z0-9]+';      // e.g. rdfs:label

        if (false == $ignoreVariables) {
            $regex .= '|\?[a-z0-9\_]+'; // e.g. ?s
        }

        $regex .= ')';
        return $regex;
    }

    /**
     * Returns the regex string to get the subject part of a RDF triple/quad.
     *
     * @param boolean $ignoreVariables optional, default is false
     * @return string
     */
    public function getRegexStringForSubjects($ignoreVariables = false)
    {
        $regex = '(' .
            '\<[a-z0-9\.\/\:#\-_]+\>|' . // e.g. <http://foobar/a>
            '[a-z0-9]+\:[a-z0-9]+|' .    // e.g. rdfs:label
            '_:[a-z0-9]+';              // e.g. _:foo

        if (false == $ignoreVariables) {
            $regex .= '|\?[a-z0-9\_]+'; // e.g. ?s
        }

        $regex .= ')';
        return $regex;
    }
}
