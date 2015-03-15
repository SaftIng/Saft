<?php
namespace Saft\Backend\Redland\Rdf;

use \Saft\Rdf\AbstractNamedNode;

class NamedNode extends AbstractNamedNode
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
     * @return string
     */
    public function getValue()
    {
        return librdf_uri_to_string(librdf_node_get_uri($this->redlandNode));
    }
}
