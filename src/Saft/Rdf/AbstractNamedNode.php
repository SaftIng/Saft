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
abstract class AbstractNamedNode implements NamedNode
{
    /**
     * This method is ment for getting some kind of human readable string
     * representation of the current node. It returns the URI of this instance.
     *
     * @return string the stored URI
     *
     * @api
     *
     * @since 0.1
     */
    public function __toString()
    {
        return $this->getUri();
    }

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
    public function equals(Node $toCompare)
    {
        // It only compares URIs, everything else will be quit with false.
        if ($toCompare->isNamed()) {
            return $this->getUri() == $toCompare->getUri();
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
    public function matches(Node $toMatch)
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
    public function isConcrete()
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
    public function isLiteral()
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
    public function isNamed()
    {
        return true;
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
    public function isBlank()
    {
        return false;
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
    public function isPattern()
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
    public function toNQuads()
    {
        return '<'.$this->getUri().'>';
    }
}
