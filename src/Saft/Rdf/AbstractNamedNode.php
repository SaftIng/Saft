<?php

namespace Saft\Rdf;

abstract class AbstractNamedNode implements NamedNode
{
    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getUri();
    }

    /**
     * @see \Saft\Rdf\Node
     */
    public function equals(Node $toCompare)
    {
        // It only compares URIs, everything will be quit with false.
        if (true === $toCompare->isNamed()) {
            return $this->getUri() == $toCompare->getUri();
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
        return '<' . $this->getUri() . '>';
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
            return $this->getUri() === $pattern->getUri();
        } else {
            // All named nodes matches a variable/pattern
            return true;
        }
    }
}
