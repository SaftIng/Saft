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
            throw new \Exception('Parameter $uri is not a valid URI: '. $uri);
        }
    }

    /**
     * @return string URI of the node.
     */
    public function getValue()
    {
        return $this->uri;
    }
}
