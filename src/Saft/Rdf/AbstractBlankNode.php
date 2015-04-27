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
     * @see \Saft\Node
     */
    public function matches(Node $toMatch)
    {
        return $this->equals($toMatch);
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
    public function isVariable()
    {
        return false;
    }

    /**
     * @return string
     */
    public function toNQuads()
    {
        return '_:' . $this->getBlankId();
    }

    public function __toString()
    {
        return $this->toNQuads();
    }
}
