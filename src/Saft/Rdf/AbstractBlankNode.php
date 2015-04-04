<?php

namespace Saft\Rdf;

abstract class AbstractBlankNode implements BlankNode
{
    /**
     * Check if a given instance of \Saft\Rdf\Node is equal to this instance.
     *
     * @param \Saft\Rdf\Node $toCompare
     * @return boolean True, if both instances are semantically equal, false otherwise.
     */
    public function equals(\Saft\Rdf\Node $toCompare)
    {
        if ($toCompare instanceof BlankNode) {
            return $this->getBlankId() === $toCompare->getBlankId();
        }

        return false;
    }

    /**
     * @return boolean
     */
    public function isConcrete()
    {
        return true;
    }

    /**
     * @return boolean
     */
    public function isLiteral()
    {
        return false;
    }

    /**
     * @return boolean
     */
    public function isNamed()
    {
        return false;
    }

    /**
     * @return boolean
     */
    public function isBlank()
    {
        return true;
    }

    /**
     * @return boolean
     */
    public function isReturnable()
    {
        return true;
    }
    
    /**
     * @return boolean
     */
    public function isVariable()
    {
        return false;
    }

    /**
     * @return string
     */
    public function toNQuads()
    {
        return "_:" . $this->getBlankId();
    }

    /**
     * A blank node matches another blank node, if there blank ids are equal.
     * {@inheritdoc}
     * @throws \Exception when $pattern is neither an instance of BlankNode nor Variable
     */
    final public function matches(Node $pattern)
    {
        if (!($pattern instanceof BlankNode || $pattern instanceof Variable)) {
            throw new \Exception('$pattern must be of type BlankNode or Variable');
        }

        if ($pattern->isConcrete()) {
            return $this->getBlankId() === $pattern->getBlankId();
        } else {
            // All BlankNodes matches a variable/pattern
            return true;
        }
    }

    public function __toString()
    {
        return $this->toNQuads();
    }
}
