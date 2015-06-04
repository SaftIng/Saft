<?php

namespace Saft\Addition\Redland\Rdf;

use Saft\Rdf\AbstractBlankNode;

class BlankNode extends AbstractBlankNode
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

    public function getRedlandNode()
    {
        return $this->redlandNode;
    }
}
