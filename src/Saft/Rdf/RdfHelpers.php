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

namespace Saft\Rdf;

/**
 * Class which provides useful methods for RDF related operations, for instance node creation or
 * URI checks.
 *
 * @api
 *
 * @since 0.9
 */
class RdfHelpers
{
    /**
     * @param string $s Literal string to encode
     *
     * @return string encoded string for n-quads
     */
    public function encodeStringLiteralForNQuads($s): string
    {
        $s = \str_replace('\\', '\\\\', $s);
        $s = \str_replace("\t", '\t', $s);
        $s = \str_replace("\n", '\n', $s);
        $s = \str_replace("\r", '\r', $s);
        $s = \str_replace('"', '\"', $s);

        return $s;
    }

    /**
     * @param string $stringToCheck
     *
     * @return null|string
     */
    public function guessFormat(string $stringToCheck)
    {
        $short = \substr($stringToCheck, 0, 1024);

        // n-triples/n-quads
        if (0 < \preg_match('/^<.+>/i', $short, $matches)) {
            return 'n-triples';

        // RDF/XML
        } elseif (0 < \preg_match('/<rdf:/i', $short, $matches)) {
            return 'rdf-xml';

        // turtle
        } elseif (0 < \preg_match('/@prefix\s|@base\s/i', $short, $matches)) {
            return 'turtle';
        }

        return null;
    }

    /**
     * Checks if a given string is a blank node ID. Blank nodes are usually structured like
     * _:foo, whereas _: comes first always.
     *
     * @param string $string string to check if its a blank node ID or not
     *
     * @return bool true if given string is a valid blank node ID, false otherwise
     */
    public function simpleCheckBlankNodeId($string): bool
    {
        return '_:' == \substr($string, 0, 2);
    }

    /**
     * Checks the general syntax of a given URI. Protocol-specific syntaxes are not checked. Instead, only
     * characters disallowed an all URIs lead to a rejection of the check. Use this function, if you need a
     * basic check and if performance is an issuse. In case you need a more precise check, that function is
     * not recommended.
     *
     * @param string $string string to check if its a URI or not
     *
     * @return bool true if given string is a valid URI, false otherwise
     *
     * @api
     *
     * @since 0.1
     */
    public function simpleCheckURI($string): bool
    {
        $regEx = '/^([a-z]{2,}:[^\s]*)$/';

        return 1 === \preg_match($regEx, (string) $string);
    }
}
