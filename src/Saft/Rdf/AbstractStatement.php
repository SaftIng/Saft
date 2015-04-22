<?php

namespace Saft\Rdf;

abstract class AbstractStatement implements Statement
{
    /**
     * @return boolean
     */
    public function isConcrete()
    {
        if ($this->isQuad() && !$this->getGraph()->isConcrete()) {
            return false;
        }

        return $this->getSubject()->isConcrete()
               && $this->getPredicate()->isConcrete()
               && $this->getObject()->isConcrete();
    }

    /**
     * @return boolean
     */
    public function isPattern()
    {
        return !$this->isConcrete();
    }

    /**
     * @return boolean
     */
    public function toNQuads()
    {
        if ($this->isConcrete()) {
            if ($this->isQuad()) {
                return $this->getSubject()->toNQuads() . ' ' .
                       $this->getPredicate()->toNQuads() . ' ' .
                       $this->getObject()->toNQuads() . ' ' .
                       $this->getGraph()->toNQuads() . ' .';
            } else {
                return $this->toSparqlFormat();
            }
        } else {
            throw new \Exception('A statement has to be concrete in N-Quads.');
        }
    }

    /**
     * @return boolean
     */
    public function toSparqlFormat()
    {
        // if quad, integrate Graph, even it is null
        if ($this->isQuad()) {
            return 'Graph '. $this->getGraph()->toNQuads() .' {'.
                   $this->getSubject()->toNQuads() .' '.
                   $this->getPredicate()->toNQuads() .' '.
                   $this->getObject()->toNQuads() .
                   '}';
        } else {
            return $this->getSubject()->toNQuads() .' '.
                   $this->getPredicate()->toNQuads() .' '.
                   $this->getObject()->toNQuads() . ' .';
        }
    }

    public function __toString()
    {
        return $this->toSparqlFormat();
    }

    /**
     * {@inheritdoc}
     */
    public function matches(Statement $pattern)
    {
        if ($this->isPattern()) {
            throw new \LogicException('This must be concrete');
        }

        $subjectsMatch = $this->getSubject()->matches($pattern->getSubject());
        $predicatesMatch = $this->getPredicate()->matches($pattern->getPredicate());
        $objectsMatch = $this->getObject()->matches($pattern->getObject());
        if ($this->isQuad() && $pattern->isQuad()) {
            $graphsMatch = $this->getGraph()->matches($pattern->getGraph());
        } elseif ($this->isQuad() && $pattern->isTriple()) {
            $graphsMatch = true;
        } elseif ($this->isTriple() && $pattern->isQuad()) {
            $graphsMatch = !$pattern->getGraph()->isConcrete();
        } elseif ($this->isTriple() && $pattern->isTriple()) {
            $graphsMatch = true;
        }

        return $subjectsMatch && $predicatesMatch && $objectsMatch && $graphsMatch;
    }
}
