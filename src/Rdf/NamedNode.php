<?php
namespace Saft\Backend\Redland\Rdf;

use \Saft\Rdf\AbstractNamedNode;

class NamedNode extends AbstractNamedNode
{
    /**
     * @var librdf_node the wrapped redland node
     */
    protected $redlandNode;

    public function __construct($value)
    {
        if ($value === null) {
            throw new \Exception("Can't initialize node with null.");
        }
        if (gettype($value) == "resource" && get_resource_type($value) == "_p_librdf_node_s") {
            $this->redlandNode = $value;
        } else {
            $world = librdf_new_world();
            $uri = librdf_new_uri($world, $value);
            $this->redlandNode = librdf_new_node_from_uri($world, $uri);
        }
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return librdf_uri_to_string(librdf_node_get_uri($this->redlandNode));
    }
}
