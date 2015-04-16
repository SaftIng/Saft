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
     * @param string $lang optional Will be ignore because a NamedNode has no language.
     * @throws \Exception If parameter $value is not a valid URI.
     */
    public function __construct($uri, $lang = null)
    {
        if (true === self::check($uri) || null === $uri) {
            $this->uri = $uri;
        } else {
            throw new \Exception('First parameter ($uri) is not a valid URI.');
        }
    }

    /**
     * @return string URI of the node.
     */
    public function getUri()
    {
        return $this->uri;
    }
}
