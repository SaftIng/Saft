<?php
namespace Saft\Backend\Redland\Rdf;

use \Saft\Rdf\AbstractLiteral;

class Literal extends AbstractLiteral
{
    /**
     * @var librdf_node the wrapped redland node
     */
    protected $redlandNode;

    public function __construct($redlandNode)
    {
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
        return librdf_uri_to_string(librdf_node_get_literal_value_datatype_uri($this->redlandNode));
    }

    /**
     * @return string|null
     */
    public function getLanguage()
    {
        return librdf_node_get_literal_value_language($this->redlandNode);
    }
}
