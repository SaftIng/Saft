<?php

namespace Saft\Rdf;

class NamedNodeImpl extends AbstractNamedNode
{
    /**
     * @var string
     */
    protected $uri;

    /**
     * @param mixed $uri The URI of the node.
     * @param NodeUtils $nodeUtils
     * @throws \Exception If parameter $value is not a valid URI.
     */
    public function __construct(NodeUtils $nodeUtils, $uri)
    {
        if ($uri == null || !$nodeUtils->simpleCheckURI($uri)) {
            throw new \Exception('Parameter $uri is not a valid URI: '. $uri);
        }

        $this->uri = $uri;
    }

    /**
     * @return string URI of the node.
     */
    public function getUri()
    {
        return $this->uri;
    }
}
