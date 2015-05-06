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

    public function __construct($redlandNode)
    {
        if ($redlandNode === null) {
            throw new \Exception("Can't initialize literal with null.");
        }

        if (!gettype($redlandNode) == "resource" || !get_resource_type($redlandNode) == "_p_librdf_node_s") {
            throw new \Exception("Redland Literals have to be initialized with a Redland node.");
        }

        $this->redlandNode = $redlandNode;
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

    public function getRedlandNode()
    {
        return $this->redlandNode;
    }
}
