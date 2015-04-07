<?php

namespace Saft\Rdf;

abstract class AbstractNamedNode implements NamedNode
{
    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getValue();
    }

    /**
     * Checks the general syntax of a given URI. Protocol-specific syntaxes are
     * not checked. Instead, only characters disallowed an all URIs lead to a
     * rejection of the check.
     *
     * @param string $string String to check if its a URI or not.
     * @return boolean True if given string is a valid URI, false otherwise.
     */
    public static function check($string)
    {
        $regEx = '/^([a-zA-Z][a-zA-Z0-9+.-]+):([^\x00-\x0f\x20\x7f<>{}|\[\]`"^\\\\])+$/';
        return (1 === preg_match($regEx, (string)$string));
    }

    /**
     * @see \Saft\Rdf\Node
     */
    public function equals(Node $toCompare)
    {
        // It only compares URIs, everything will be quit with false.
        if (true === $toCompare->isNamed()) {
            return $this->getValue() == $toCompare->getValue();
        }

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
        return false;
    }

    /**
     * @return boolean
     */
    public function isNamed()
    {
        return true;
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
    public function isReturnable()
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
        return '<' . $this->getValue() . '>';
    }

    /**
     * A named node matches a another named node if there URIs are equals.
     * {@inheritdoc}
     * @throws \Exception when $pattern is neither an instance of NamedNode nor Variable
     */
    public function matches(Node $pattern)
    {
        if (!($pattern instanceof NamedNode || $pattern instanceof Variable)) {
            throw new \Exception('$pattern must be of type NamedNode or Variable');
        }

        if ($pattern->isConcrete()) {
            return $this->getValue() === $pattern->getValue();
        } else {
            // All named nodes matches a variable/pattern
            return true;
        }
    }
}
