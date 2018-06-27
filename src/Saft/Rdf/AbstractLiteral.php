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
abstract class AbstractLiteral implements Literal
{
    /**
     * Returns the literal value as string representation of the literal node.
     *
     * @return string a string representation of the literal
     *
     * @api
     *
     * @since 0.1
     */
    public function __toString(): string
    {
        return (string) $this->getValue();
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
    public function equals(Node $toCompare): bool
    {
        // Only compare, if given instance is a literal
        if ($toCompare->isLiteral() && $this->getDatatype()->equals($toCompare->getDatatype())) {
            return $this->getValue() === $toCompare->getValue() && $this->getLanguage() == $toCompare->getLanguage();
        }

        return false;
    }

    /**
     * Returns true, if this pattern matches the given node. This method is the same as equals for concrete nodes
     * and is overwritten for pattern/variable nodes.
     *
     * See also {@url http://www.w3.org/TR/2013/REC-sparql11-query-20130321/#matchingRDFLiterals}
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
        return false;
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
        return true;
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
        $string = '"'.$this->encodeStringLitralForNQuads($this->getValue()).'"';

        if ($this->getLanguage() !== null) {
            $string .= '@'.$this->getLanguage();
        } elseif ($this->getDatatype() !== null) {
            $string .= '^^<'.$this->getDatatype().'>';
        }

        return $string;
    }

    /**
     * @param string $s
     *
     * @return string encoded string for n-quads
     */
    protected function encodeStringLitralForNQuads($s): string
    {
        $s = str_replace('\\', '\\\\', $s);
        $s = str_replace("\t", '\t', $s);
        $s = str_replace("\n", '\n', $s);
        $s = str_replace("\r", '\r', $s);
        $s = str_replace('"', '\"', $s);

        return $s;
    }
}
