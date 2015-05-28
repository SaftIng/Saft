<?php

namespace Saft\Rdf;

class LiteralImpl extends AbstractLiteral
{
    /**
     * @var string
     */
    protected static $xsdString = 'http://www.w3.org/2001/XMLSchema#string';

    /**
     * @var string
     */
    protected static $rdfLangString = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#langString';

    /**
     * @var string
     */
    protected $value;

    /**
     * @var Node
     */
    protected $datatype = null;

    /**
     * @var string
     */
    protected $lang = null;

    /**
     * @param string $value  The Literal value
     * @param Node $datatype The datatype of the Literal (respectively defaults to xsd:string or rdf:langString)
     * @param string $lang   The language tag of the Literal (optional)
     */
    public function __construct($value, Node $datatype = null, $lang = null)
    {
        if ($value === null) {
            throw new \Exception('Literal value can\'t be null. Please use AnyPattern if you need a variable.');
        }

        /*
         * Prevent PHP from its type casting. We cast
         * - true to 'true'
         * - false to 'false'
         * - the rest to string, for instance 1 to '1', 0.1 to '0.1', ...
         */
        if (true === $value) {
            $this->value = 'true';
        } elseif (false === $value) {
            $this->value = 'false';
        } else {
            $this->value = (string)$value;
        }

        if ($lang !== null) {
            $this->lang = (string)$lang;
        }

        if (
            $lang !== null &&
            $datatype !== null &&
            $datatype->isNamed() &&
            $datatype->getUri() !== self::$rdfLangString
        ) {
            throw new \Exception('Language tagged Literals must have <'. self::$rdfLangString .'> datatype.');
        }

        if ($datatype !== null) {
            $this->datatype = $datatype;
        } elseif ($lang !== null) {
            $this->datatype = new NamedNodeImpl(self::$rdfLangString);
        } else {
            $this->datatype = new NamedNodeImpl(self::$xsdString);
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
     * Get the datatype of the Literal. It can be one of the XML Schema datatypes (XSD) or anything else. If the URI is
     * needed it can be retrieved by calling ->getDatatype()->getUri().
     *
     * An overview about all XML Schema datatypes: {@url http://www.w3.org/TR/xmlschema-2/#built-in-datatypes}
     *
     * @return Node the datatype of the Literal as named node
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
