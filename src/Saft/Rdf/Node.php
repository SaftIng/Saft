<?php

namespace Saft\Rdf;

/**
 * @api
 * @since 0.1
 */
interface Node
{
    /**
     * Checks if this instance is a literal.
     *
     * @return boolean True, if it is a literal, false otherwise.
     * @api
     * @since 0.1
     */
    public function isLiteral();

    /**
     * Checks if this instance is a named node.
     *
     * @return boolean True, if it is a named node, false otherwise.
     * @api
     * @since 0.1
     */
    public function isNamed();

    /**
     * Checks if this instance is a blank node.
     *
     * @return boolean True, if this instance is a blank node, false otherwise.
     * @api
     * @since 0.1
     */
    public function isBlank();

    /**
     * Checks if this instance is concrete, which means it does not contain pattern.
     *
     * @return boolean True, if this instance is concrete, false otherwise.
     * @api
     * @since 0.1
     */
    public function isConcrete();

    /**
     * Checks if this instance is a pattern. It can either be a pattern or concrete.
     *
     * @return boolean True, if this instance is a pattern, false otherwise.
     * @api
     * @since 0.1
     */
    public function isPattern();

    /**
     * Transform this Node instance to a n-quads string, if possible.
     *
     * @return string N-quads string representation of this instance.
     * @throws \Exception if no n-quads representation is available.
     * @api
     * @since 0.1
     */
    public function toNQuads();

    /**
     * This method is ment for getting some kind of human readable string
     * representation of the current node. There is no definite syntax, but it
     * should contain the the URI for NamedNodes and the value for Literals.
     *
     * @return string A human readable string representation of the node.
     * @api
     * @since 0.1
     */
    public function __toString();

    /**
     * Check if a given instance of Node is equal to this instance.
     *
     * @param Node $toCompare Node instance to check against.
     * @return boolean True, if both instances are semantically equal, false otherwise.
     * @api
     * @since 0.1
     */
    public function equals(Node $toCompare);

    /**
     * Returns true, if this pattern matches the given node. This method is the same as equals for concrete nodes
     * and is overwritten for pattern/variable nodes.
     *
     * @param Node $toMatch Node instance to apply the pattern on.
     * @return boolean true, if this pattern matches the node, false otherwise.
     * @api
     * @since 0.1
     */
    public function matches(Node $toMatch);
}
