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
        return (string)$this->getValue();
    }

    /**
     * @see \Saft\Node
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
     * see also {@url http://www.w3.org/TR/2013/REC-sparql11-query-20130321/#matchingRDFLiterals}
     * @see \Saft\Node
     */
    public function matches(Node $toMatch)
    {
        return $this->equals($toMatch);
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
        $string = '"' . $this->getValue() . '"';

        // handle boolean values when transformed as n-triples by setting a
        if (is_bool($this->getValue())) {
            $string = $this->getValue()?'"true"':'"false"';
        }

        if ($this->getLanguage() !== null) {
            $string .= '@' . $this->getLanguage();
        } elseif ($this->getDatatype() !== null) {
            $string .= '^^<' . $this->getDatatype() . '>';
        }

        return $string;
    }
}
