<?php

namespace Saft\Rdf;

/**
 * This is a specific variable node, which is meant to match certain constructs of literal nodes by specifying
 * parts of the literal.
 * see also {@url http://www.w3.org/TR/2013/REC-sparql11-query-20130321/#matchingRDFLiterals}
 */
class LiteralPatternImpl implements Node
{
    protected $value;
    protected $datatype;
    protected $language;

    /**
     * @param string $value    The Literal value
     * @param Node   $datatype The datatype of the Literal (respectively defaults to xsd:string or rdf:langString)
     * @param string $lang     The language tag of the Literal (optional)
     */
    public function __construct($value, $datatype, $language = null)
    {
        $this->value = $value;
        $this->datatype = $datatype;

        if (null !== $language) {
            $this->language = $language;
        }
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
     * @param  Node    $toCompare
     * @return boolean True if this instance and $toCompare match with value, datatype and language,
     *                 false otherwise.
     */
    public function equals(Node $toCompare)
    {
        // TODO to be done
        // Only compare, if given instance is a literalpattern too
        if ($toCompare instanceof LiteralPatternImpl) {
            return $this->getValue() === $toCompare->getValue()
                && $this->getDatatype() === $toCompare->getDatatype()
                && $this->getLanguage() === $toCompare->getLanguage();
        }
        return false;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getDatatype()
    {
        return $this->datatype;
    }

    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * A literal matches only another literal if its value, datatype and language are equal.
     *
     * @param  Node    $toMatch Node instance to apply the pattern on
     * @return boolean true, if this pattern matches the node, false otherwise
     * @todo check if that could be deleted
     */
    public function matches(Node $toMatch)
    {
        if ($toMatch->isConcrete()) {
            if ($toMatch instanceof Literal) {
                return $this->getValue() === $toMatch->getValue()
                    && $this->getDatatype() === $toMatch->getDatatype()
                    && $this->getLanguage() === $toMatch->getLanguage();
            }
            return false;

        } else {
            throw new \Exception('The node to match has to be a concrete node');
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
