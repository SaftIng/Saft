<?php

namespace Saft\Rdf;

class NodeUtils
{
    /**
     * Forked from Erfurt_Utils.php of the Erfurt project.
     *
     * Build a Turtle-compatible literal string out of an RDF/PHP array object.
     * This string is used as the canonical representation for object values in Erfurt.
     *
     * @see {http://www.w3.org/TeamSubmission/turtle/}
     * @param string $value             Value of the later triple
     * @param string $datatype optional Data type of the $value (XML-Datatype URL)
     * @param string $lang     optional Language of the $value
     * @return string
     */
    public static function buildLiteralString($value, $datatype = null, $lang = null)
    {
        $longString = false;
        $quoteChar  = (strpos($value, '"') !== false) ? "'" : '"';
        $value      = (string)$value;

        // datatype-specific treatment
        switch ($datatype) {
            case "http://www.w3.org/2001/XMLSchema#boolean":
                // it seems that either Virtuoso or ODBC convert a xmls:boolean
                // value to an integer later on. So we will cast it internally
                // to an string, to keep the value, but, unfortunately, we lost
                // the datatype too.
                $search  = array("0", "1");
                $replace = array("false", "true");
                $value   = str_replace($search, $replace, $value);

                $datatype = "http://www.w3.org/2001/XMLSchema#string";
                break;

            /* no normalization needed for these types */
            case "http://www.w3.org/2001/XMLSchema#decimal":
                break;
            case "http://www.w3.org/2001/XMLSchema#integer":
                break;
            case "http://www.w3.org/2001/XMLSchema#int":
                break;
            case "http://www.w3.org/2001/XMLSchema#float":
                break;
            case "http://www.w3.org/2001/XMLSchema#double":
                break;
            case "http://www.w3.org/2001/XMLSchema#duration":
                break;
            case "http://www.w3.org/2001/XMLSchema#dateTime":
                break;
            case "http://www.w3.org/2001/XMLSchema#date":
                break;
            case "http://www.w3.org/2001/XMLSchema#gMonthDay":
                break;
            case "http://www.w3.org/2001/XMLSchema#anyURI":
                break;
            case "http://www.w3.org/2001/XMLSchema#time":
                break;
            /* no normalization needed for these types */
            case "":    /* fallthrough */
            case null:  /* fallthrough */
            case "http://www.w3.org/1999/02/22-rdf-syntax-ns#XMLLiteral":   /* fallthrough */
            case "http://www.w3.org/2001/XMLSchema#string":
            default:
                $value = addcslashes($value, $quoteChar);

                /**
                 * TODO Check for characters not allowed in a short literal
                 * {@link http://www.w3.org/TR/rdf-sparql-query/#rECHAR}
                 */
                if ($pos = preg_match('/[\x5c\r\n"]/', $value)) {
                    $longString = true;
                }
                break;
        }

        // add short, long literal quotes respectively
        $value = $quoteChar . ($longString ? ($quoteChar . $quoteChar) : '')
               . $value
               . $quoteChar . ($longString ? ($quoteChar . $quoteChar) : '');

        // add datatype URI/lang tag
        if (!empty($datatype)) {
            $value .= '^^<' . (string)$datatype . '>';
        } elseif (!empty($lang)) {
            $value .= '@' . (string)$lang;
        }

        return $value;
    }

    /**
     * It checks the given $datatype and casts the given value the right way.
     * That is important if you want to keep the type of the data synchronized
     * with PHP variable types.
     *
     * @param string $datatype
     * @param scalar $value
     * @return mixed
     * @throws \Exception If an unknown datatype was given.
     */
    public static function getRealValueBasedOnDatatype($datatype, $value)
    {
        switch($datatype) {
            // xsd:boolean
            case 'http://www.w3.org/2001/XMLSchema#boolean':
                return new LiteralImpl((boolean)$value);

            // xsd:float
            case 'http://www.w3.org/2001/XMLSchema#float':
                return new LiteralImpl(floatval($value));

            // xsd:integer
            case 'http://www.w3.org/2001/XMLSchema#integer':
                return new LiteralImpl((int)$value);

            // xsd:string
            case 'http://www.w3.org/2001/XMLSchema#string':
                return new LiteralImpl('"'. $value .'"');

            default:
                throw new \Exception('Unknown $datatype given.');
        }
    }


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
