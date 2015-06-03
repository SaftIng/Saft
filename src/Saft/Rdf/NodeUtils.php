<?php

namespace Saft\Rdf;

class NodeUtils
{
    /**
     * Checks the general syntax of a given URI. Protocol-specific syntaxes are
     * not checked. Instead, only characters disallowed an all URIs lead to a
     * rejection of the check.
     *
     * @param string $string String to check if its a URI or not.
     * @return boolean True if given string is a valid URI, false otherwise.
     */
    public static function simpleCheckURI($string)
    {
        $regEx = '/^([a-zA-Z][a-zA-Z0-9+.-]+):([^\x00-\x0f\x20\x7f<>{}|\[\]`"^\\\\])+$/';
        return (1 === preg_match($regEx, (string)$string));
    }
}
