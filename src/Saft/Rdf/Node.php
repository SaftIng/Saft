<?php

namespace Saft\Rdf;

interface Node
{
    /**
     * @return boolean
     */
    public function isLiteral();

    /**
     * @return boolean
     */
    public function isNamed();

    /**
     * Checks if this instance is a blank node.
     *
     * @return boolean
     */
    public function isBlank();

    /**
     * Checks if this instance is concrete, which means it does not contain pattern.
     *
     * @return boolean
     */
    public function isConcrete();

    /**
     * Checks if this instance is a pattern.
     *
     * @return boolean
     */
    public function isPattern();

    /**
     * @return string
     */
    public function toNQuads();

    /**
     * This method is ment for getting some kind of human readable string
     * representation of the current node. There is no definite syntax, but it
     * should contain the the URI for NamedNodes and the value for Literals.
     *
     * @return string a human readable string representation of the node
     */
    public function __toString();

    /**
     * Check if a given instance of Node is equal to this instance.
     *
     * @param  Node    $toCompare Node instance to check against.
     * @return boolean            True, if both instances are semantically equal, false otherwise.
     */
    public function equals(Node $toCompare);

    /**
     * Returns true, if this pattern matches the given node.
     * This method is the same as equals for concrete nodes and is overwritten for pattern/variable nodes.
     *
     * @param  Node $toMatch Node instance to apply the pattern on
     * @return boolean true, if this pattern matches the node, false otherwise
     */
    public function matches(Node $toMatch);
}
