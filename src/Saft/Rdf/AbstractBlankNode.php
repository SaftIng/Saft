<?php

namespace Saft\Rdf;

/**
 * @api
 */
abstract class AbstractBlankNode implements BlankNode
{
    /**
     * Check if a given instance of Node is equal to this instance.
     *
     * @param Node $toCompare Node instance to check against.
     * @return boolean True, if both instances are semantically equal, false otherwise.
     */
    public function equals(Node $toCompare)
    {
        if ($toCompare instanceof BlankNode) {
            return $this->getBlankId() === $toCompare->getBlankId();
        }

        return false;
    }

    /**
     * Returns true, if this pattern matches the given node. This method is the same as equals for concrete nodes
     * and is overwritten for pattern/variable nodes.
     *
     * @param Node $toMatch Node instance to apply the pattern on
     * @return boolean true, if this pattern matches the node, false otherwise
     */
    public function matches(Node $toMatch)
    {
        return $this->equals($toMatch);
    }

    /**
     * Checks if this instance is concrete, which means it does not contain pattern.
     *
     * @return boolean True, if this instance is concrete, false otherwise.
     */
    public function isConcrete()
    {
        return true;
    }

    /**
     * Checks if this instance is a literal.
     *
     * @return boolean True, if it is a literal, false otherwise.
     */
    public function isLiteral()
    {
        return false;
    }

    /**
     * Checks if this instance is a named node.
     *
     * @return boolean True, if it is a named node, false otherwise.
     */
    public function isNamed()
    {
        return false;
    }

    /**
     * Checks if this instance is a blank node.
     *
     * @return boolean True, if this instance is a blank node, false otherwise.
     */
    public function isBlank()
    {
        return true;
    }

    /**
     * Checks if this instance is a pattern. It can either be a pattern or concrete.
     *
     * @return boolean True, if this instance is a pattern, false otherwise.
     */
    public function isPattern()
    {
        return false;
    }

    /**
    * Transform this Node instance to a n-quads string, if possible.
    *
    * @return string N-quads string representation of this instance.
     */
    public function toNQuads()
    {
        return '_:' . $this->getBlankId();
    }

    /**
     * This method is ment for getting some kind of human readable string
     * representation of the current node.
     *
     * @return string a human readable string representation of the node
     */
    public function __toString()
    {
        return $this->toNQuads();
    }
}
