<?php

namespace Saft\Rdf;

/**
 * Interface Variable
 */
class AnyPatternImpl implements Node
{
    /**
     * @return boolean
     */
    public function isBlank()
    {
        return false;
    }

    /**
     * @return boolean
     */
    public function isConcrete()
    {
        return false;
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
    public function isVariable()
    {
        return true;
    }

    /**
     * @see \Saft\Node
     */
    public function equals(Node $toCompare)
    {
        // Only compare, if given instance is a literal
        if ($toCompare instanceof AnyPatternImpl) {
            return true;
        }
        return false;
    }

    /**
     * This method matches any node
     */
    public function matches(Node $toMatch)
    {
        return true;
    }

    /**
     * Returns "ANY" as string representation
     */
    public function __toString()
    {
        return "ANY";
    }

    public function toNQuads()
    {
        throw new \Exception("The AnyPattern is not valid in NQuads");
    }
}
