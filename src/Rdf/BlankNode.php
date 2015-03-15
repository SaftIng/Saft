<?php
namespace Saft\Backend\Redland\Rdf;

use \Saft\Rdf\AbstractBlankNode;

class Literal extends AbstractBlankNode
{
    /**
     * @var librdf_node the wrapped redland node
     */
    protected $redlandNode;

    public function __construct($redlandNode)
    {
        $this->redlandNode = $redlandNode;
    }

    public function getBlankId()
    {
        return librdf_node_get_blank_identifier($this->redlandNode);
    }
}
