<?php

namespace Saft\Rdf;

class NamedNodeImpl extends AbstractNamedNode
{
    /**
     * @var string
     */
    protected $uri;

    /**
     * @param RdfHelpers $rdfHelpers
     * @param string $uri The URI of the node.
     * @throws \Exception If parameter $value is not a valid URI.
     */
    public function __construct(RdfHelpers $rdfHelpers, $uri)
    {
        if ($uri == null || !is_string($uri) || !$rdfHelpers->simpleCheckURI($uri)) {
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
