<?php
namespace Saft\Backend\Redland\Rdf;

use \Saft\Rdf\AbstractLiteral;

class Literal extends AbstractLiteral
{
    protected static $xsdString = "http://www.w3.org/2001/XMLSchema#string";
    protected static $rdfLangString = "http://www.w3.org/1999/02/22-rdf-syntax-ns#langString";

    /**
     * @var librdf_node the wrapped redland node
     */
    protected $redlandNode;

    public function __construct($value, $datatype = null, $lang = null)
    {
        if ($value === null) {
            throw new \Exception("Can't initialize literal with null as value.");
        }
        if (gettype($value) == "resource" && get_resource_type($value) == "_p_librdf_node_s") {
            $this->redlandNode = $value;
        } else {

            if ($lang !== null && $datatype !== null && $datatype !== self::$rdfLangString) {
                throw new \Exception(
                    "Language tagged Literals must have " .
                    "<" . self::$rdfLangString . "> " .
                    "datatype."
                );
            }

            $world = librdf_new_world();
            if ($datatype !== null && $lang === null) {
                // TODO catch invalid URIs
                $datatypeUri = librdf_new_uri($world, $datatype);
            } else {
                $datatypeUri = null;
            }

            /*
             * This redland method does only support either $lang or $datatypeUri or both null
             */
            $this->redlandNode = librdf_new_node_from_typed_literal($world, $value, $lang, $datatypeUri);
            if ($this->redlandNode === null) {
                throw new \Exception("Initialization of redland node failed");
            }
        }
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return librdf_node_get_literal_value($this->redlandNode);
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getDatatype()
    {
        $datatype = librdf_node_get_literal_value_datatype_uri($this->redlandNode);
        if ($datatype !== null) {
            return librdf_uri_to_string($datatype);
        } elseif ($this->getLanguage() !== null) {
            return self::$rdfLangString;
        } else {
            return self::$xsdString;
        }
    }

    /**
     * @return string|null
     */
    public function getLanguage()
    {
        return librdf_node_get_literal_value_language($this->redlandNode);
    }
}
