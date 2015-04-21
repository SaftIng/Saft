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
            return $this->getUri() == $toCompare->getValue();
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
     */
    public function matches(Node $pattern)
    {
        if (!$this->isConcrete()) {
            throw new \LogicException('This have to be concrete');
        }

        if ($pattern->isConcrete()) {
            if ($pattern instanceof NamedNode) {
                return $this->getUri() === $pattern->getUri();
            } else {
                return false;
            }
        } else {
            // All named nodes matches a variable/pattern
            return true;
        }
    }
}
