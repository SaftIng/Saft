<?php

namespace Saft\Rdf;

class NamedNodeImpl extends AbstractNamedNode
{
    /**
     * @var string
     */
    protected $value;

    /**
     * @param mixed $value The URI of the node.
     * @param string $lang optional Will be ignore because a NamedNode has no language.
     * @throws \Exception If parameter $value is not a valid URI.
     */
    public function __construct($value, $lang = null)
    {
        if (true === self::check($value) || null === $value) {
            $this->value = $value;
        } else {
            throw new \Exception('Parameter $value is not a valid URI.');
        }
    }

    /**
     * @return string URI of the node.
     */
    public function getValue()
    {
        return $this->value;
    }
}
