<?php

namespace Saft\Rdf;

class LiteralImpl extends AbstractLiteral
{

    protected static $xsdString = "http://www.w3.org/2001/XMLSchema#string";
    protected static $rdfLangString = "http://www.w3.org/1999/02/22-rdf-syntax-ns#langString";

    /**
     * @var string
     */
    protected $lang;

    /**
     * @var string
     */
    protected $value;

    /**
     * @var string
     */
    protected $datatype;

    /**
     * @param mixed $value the Literal value
     * @param string $datatype the datatype URI for the Literal
     * @param string $lang the language tag of the Literal (optional)
     */
    public function __construct($value, $datatype = null, $lang = null)
    {
        if ($value === null) {
            throw new \Exception("Literal value can't be null.");
        }

        $this->value = $value;
        $this->lang = $lang;

        if ($lang !== null && $datatype !== null && $datatype !== self::$rdfLangString) {
            throw new \Exception(
                "Language tagged Literals must have " .
                "<" . self::$rdfLangString . "> " .
                "datatype."
            );
        }

        if ($datatype !== null) {
            $this->datatype = $datatype;
        } elseif ($lang !== null) {
            $this->datatype = self::$rdfLangString;
        } else {
            $this->datatype = self::$xsdString;
        }
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Get the datatype URI of the Literal. It can be one of the XML Schema
     * datatypes (XSD) or anything else.
     *
     * An overview about all XML Schema datatypes:
     * {@url http://www.w3.org/TR/xmlschema-2/#built-in-datatypes}
     * @return string the URI of the datatype of the Literal
     */
    public function getDatatype()
    {
        return $this->datatype;
    }

    /**
     * @return string|null
     */
    public function getLanguage()
    {
        return $this->lang;
    }
}
