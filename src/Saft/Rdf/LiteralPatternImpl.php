<?php

namespace Saft\Rdf;

/**
 * This is a specific variable node, which is meant to match certain constructs of literal nodes by specifying parts of
 * the literal.
 * see also {@url http://www.w3.org/TR/2013/REC-sparql11-query-20130321/#matchingRDFLiterals}
 */
class LiteralPatternImpl implements Node
{
    protected $value;
    protected $datatype;
    protected $language;

    public function __construct($value, $datatype, $language)
    {
        $this->value = $value;
        $this->datatype = $datatype;
        $this->language = $language;
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
        // TODO to be done
        // Only compare, if given instance is a literal
        if ($toCompare instanceof LiteralPatternImpl) {
            return $this->getValue() === $toMatch->getValue()
                && $this->getDatatype() === $toMatch->getDatatype()
                && $this->getLanguage() === $toMatch->getLanguage();
        }
        return false;
    }


    /**
     * Literal
     * A literal matches only another literal if there values, datatypes and languages are equal.
     *
     * @param  Node $toMatch Node instance to apply the pattern on
     * @return boolean true, if this pattern matches the node, false otherwise
     */
    public function matches(Node $toMatch)
    {
        // TODO to be done
        if (!$toMatch->isConcrete()) {
            throw new \Exception('The node to match has to be a concrete node');
        }

        if ($toMatch->isConcrete()) {
            if ($toMatch instanceof Literal) {
                return $this->getValue() === $toMatch->getValue()
                    && $this->getDatatype() === $toMatch->getDatatype()
                    && $this->getLanguage() === $toMatch->getLanguage();
            }

            return false;
        }

    }

    /**
     * Returns a string description of the literal pattern representation
     */
    public function __toString()
    {
        // TODO to be done
        return "LITERALPATTERN";
    }

    public function toNQuads()
    {
        throw new \Exception("The LiteralPattern is not valid in NQuads");
    }
}
