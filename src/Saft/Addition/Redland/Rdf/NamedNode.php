<?php

namespace Saft\Addition\Redland\Rdf;

use Saft\Rdf\AbstractNamedNode;
use Saft\Rdf\NodeUtils;

class NamedNode extends AbstractNamedNode
{
    /**
     * @var librdf_node the wrapped redland node
     */
    protected $redlandNode;

    /**
     * @param NodeUtils $nodeUtils
     */
    public function __construct($redlandNode)
    {
        if ($redlandNode === null) {
            throw new \Exception('Can\'t initialize node with null.');
        }
        if (!gettype($redlandNode) == 'resource' || !get_resource_type($redlandNode) == '_p_librdf_node_s') {
            throw new \Exception('Redland NamedNodes have to be initialized with a Redland node.');
        }

        $this->redlandNode = $redlandNode;
    }

    /**
     * @return string
     */
    public function getUri()
    {
        return librdf_uri_to_string(librdf_node_get_uri($this->redlandNode));
    }

    public function getRedlandNode()
    {
        return $this->redlandNode;
    }
}
