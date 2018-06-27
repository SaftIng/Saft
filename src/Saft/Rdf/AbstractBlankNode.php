<?php

/*
 * This file is part of Saft.
 *
 * (c) Konrad Abicht <hi@inspirito.de>
 * (c) Natanael Arndt <arndt@informatik.uni-leipzig.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Saft\Rdf;

/**
 * @api
 *
 * @since 0.1
 */
abstract class AbstractBlankNode implements BlankNode
{
    /**
     * Check if a given instance of Node is equal to this instance.
     *
     * @param Node $toCompare node instance to check against
     *
     * @return bool true, if both instances are semantically equal, false otherwise
     *
     * @api
     *
     * @since 0.1
     */
    public function equals(Node $toCompare): bool
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
     *
     * @return bool true, if this pattern matches the node, false otherwise
     *
     * @api
     *
     * @since 0.1
     */
    public function matches(Node $toMatch): bool
    {
        return $this->equals($toMatch);
    }

    /**
     * Checks if this instance is concrete, which means it does not contain pattern.
     *
     * @return bool true, if this instance is concrete, false otherwise
     *
     * @api
     *
     * @since 0.1
     */
    public function isConcrete(): bool
    {
        return true;
    }

    /**
     * Checks if this instance is a literal.
     *
     * @return bool true, if it is a literal, false otherwise
     *
     * @api
     *
     * @since 0.1
     */
    public function isLiteral(): bool
    {
        return false;
    }

    /**
     * Checks if this instance is a named node.
     *
     * @return bool true, if it is a named node, false otherwise
     *
     * @api
     *
     * @since 0.1
     */
    public function isNamed(): bool
    {
        return false;
    }

    /**
     * Checks if this instance is a blank node.
     *
     * @return bool true, if this instance is a blank node, false otherwise
     *
     * @api
     *
     * @since 0.1
     */
    public function isBlank(): bool
    {
        return true;
    }

    /**
     * Checks if this instance is a pattern. It can either be a pattern or concrete.
     *
     * @return bool true, if this instance is a pattern, false otherwise
     *
     * @api
     *
     * @since 0.1
     */
    public function isPattern(): bool
    {
        return false;
    }

    /**
     * Transform this Node instance to a n-quads string, if possible.
     *
     * @return string N-quads string representation of this instance
     *
     * @api
     *
     * @since 0.1
     */
    public function toNQuads(): string
    {
        return '_:'.$this->getBlankId();
    }

    /**
     * This method is ment for getting some kind of human readable string
     * representation of the current node.
     *
     * @return string a human readable string representation of the node
     *
     * @api
     *
     * @since 0.1
     */
    public function __toString(): string
    {
        return $this->toNQuads();
    }
}
