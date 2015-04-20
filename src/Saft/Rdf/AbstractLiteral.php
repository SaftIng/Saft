<?php

namespace Saft\Rdf;

abstract class AbstractLiteral implements Literal
{
    /**
     * Returns the literal value as string representation of the literal node
     *
     * @return string a string representation of the literal
     */
    public function __toString()
    {
        return $this->getValue();
    }

    /**
     * @see \Saft\Node
     */
    public function equals(Node $toCompare)
    {
        // Only compare, if given instance is a literal
        if ($toCompare->isLiteral()) {
            if ($this->getDatatype() == $toCompare->getDatatype()) {
                return $this->getValue() == $toCompare->getValue();
            }
        }
        return false;
    }

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
        return true;
    }

    /**
     * @return boolean
     */
    public function isLiteral()
    {
        return true;
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
        return false;
    }

    /**
     * @return string
     */
    public function toNQuads()
    {
        // handle boolean values when transformed as n-triples by setting a
        if (is_bool($this->getValue())) {
            $string = $this->getValue()?'"true"':'"false"';
        } else {
            $string = '"' . $this->getValue() . '"';
        }

        if ($this->getLanguage() !== null) {
            $string .= '@' . $this->getLanguage();
        } elseif ($this->getDatatype() !== null) {
            $string .= '^^<' . $this->getDatatype() . '>';
        }

        return $string;
    }

    /**
     * A literal matches only another literal if there values, datatypes and languages are equal.
     *
     * {@inheritdoc}
     */
    public function matches(Node $pattern)
    {
        if (!$this->isConcrete()) {
            throw new \LogicException('This have to be concrete');
        }

        if ($pattern->isConcrete()) {
            if ($pattern instanceof Literal) {
                return $this->getValue() === $pattern->getValue()
                    && $this->getDatatype() === $pattern->getDatatype()
                    && $this->getLanguage() === $pattern->getLanguage();
            } else {
                return false;
            }
        } else {
            // All Literals matches a variable/pattern
            return true;
        }
    }
}
