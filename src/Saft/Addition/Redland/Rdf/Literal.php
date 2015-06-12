<?php

namespace Saft\Addition\Redland\Rdf;

use Saft\Rdf\AbstractLiteral;

class Literal extends AbstractLiteral
{
    protected static $xsdString = 'http://www.w3.org/2001/XMLSchema#string';
    protected static $rdfLangString = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#langString';

    /**
     * @var librdf_node the wrapped redland node
     */
    protected $redlandNode;

    public function __construct($redlandNode)
    {
        if ($redlandNode === null) {
            throw new \Exception('Can\'t initialize literal with null.');
        }

        if (!gettype($redlandNode) == 'resource' || !get_resource_type($redlandNode) == '_p_librdf_node_s') {
            throw new \Exception('Redland Literals have to be initialized with a Redland node.');
        }

        $this->redlandNode = $redlandNode;
    }

    /**
     * @return string
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
        $nodeFactory = new NodeFactory();
        $datatypeUri = self::$xsdString;
        $datatype = librdf_node_get_literal_value_datatype_uri($this->redlandNode);
        if ($datatype !== null) {
            $datatypeUri = librdf_uri_to_string($datatype);
        } elseif ($this->getLanguage() !== null) {
            $datatypeUri = self::$rdfLangString;
        }

        return $nodeFactory->createNamedNode($datatypeUri);
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
