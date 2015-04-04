<?php

namespace Saft\Rdf;

abstract class AbstractLiteral implements Literal
{
    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getValue();
    }

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
     * @see \Saft\Node
     */
    public function equals(Node $toCompare)
    {
        // Only compare, if given instance is a literal
        if (true == $toCompare->isLiteral()) {
            return $this->getValue() === $toCompare->getValue();
        }

        // TODO what about cases like 1 == 1.0 or 1 == "1"?

        return false;
    }

    /**
     * @return boolean
     */
    public function isBlank()
    {
        return false;
    }

    /**
     * @return boolean
     */
    public function isConcrete()
    {
        return true;
    }

    /**
     * @return boolean
     */
    public function isLiteral()
    {
        return true;
    }

    /**
     * @return boolean
     */
    public function isNamed()
    {
        return false;
    }

    /**
     * @return boolean
     */
    public function isReturnable()
    {
        return false;
    }

    /**
     * @return boolean
     */
    public function isVariable()
    {
        return false;
    }

    /**
     * @return string
     */
    public function toNQuads()
    {
        // TODO how to handle boolean values when to transformed as n-tuple?
        if ('http://www.w3.org/2001/XMLSchema#boolean' == $this->getDatatype()) {
            if (true === $this->getValue()) {
                $string = '"true"';
            } else {
                $string = '"false"';
            }
        } else {
            $string = '"' . $this->getValue() . '"';
        }

        if ($this->getLanguage() !== null) {
            $string .= '@' . $this->getLanguage();
        } elseif ($this->getDatatype() !== null) {
            $string .= '^^<' . $this->getDatatype() . '>';
        }

        return $string;
    }

    /**
     * A literal matches only another literal if there values, datatypes and
     * languages are equal.
     * {@inheritdoc}
     * @throws \Exception when $pattern is neither an instance of Literal nor Variable
     */
    public function matches(Node $pattern)
    {
        if (!($pattern instanceof Literal || $pattern instanceof Variable)) {
            throw new \Exception('$pattern must be of type Literal or Variable');
        }

        if ($pattern->isConcrete()) {
            return $this->getValue() === $pattern->getValue()
                && $this->getDatatype() === $pattern->getDatatype()
                && $this->getLanguage() === $pattern->getLanguage();
        } else {
            // All Literals matches a variable/pattern
            return true;
        }
    }
}
