<?php

namespace Saft\Rdf;

/**
 * @api
 */
abstract class AbstractLiteral implements Literal
{
    /**
     * Returns the literal value as string representation of the literal node
     *
     * @return string a string representation of the literal
     */
    public function __toString()
    {
        return (string)$this->getValue();
    }

    /**
     * Check if a given instance of Node is equal to this instance.
     *
     * @param Node $toCompare Node instance to check against.
     * @return boolean True, if both instances are semantically equal, false otherwise.
     */
    public function equals(Node $toCompare)
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
     * @return boolean true, if this pattern matches the node, false otherwise
     */
    public function matches(Node $toMatch)
    {
        return $this->equals($toMatch);
    }

    /**
     * Checks if this instance is a blank node.
     *
     * @return boolean True, if this instance is a blank node, false otherwise.
     */
    public function isBlank()
    {
        return false;
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
        return true;
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
        $string = '"' . $this->getValue() . '"';

        if ($this->getLanguage() !== null) {
            $string .= '@' . $this->getLanguage();
        } elseif ($this->getDatatype() !== null) {
            $string .= '^^<' . $this->getDatatype() . '>';
        }

        return $string;
    }
}
