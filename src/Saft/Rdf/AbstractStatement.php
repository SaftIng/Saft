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
            return $this->toSparqlFormat();
        } else {
            throw new \Exception("A statement has to be concrete in N-Quads.");
        }
    }

    /**
     * @return boolean
     */
    public function toSparqlFormat()
    {
        if ($this->isQuad()) {
            return $this->getSubject()->toNQuads() . ' ' .
                   $this->getPredicate()->toNQuads() . ' ' .
                   $this->getObject()->toNQuads() . ' ' .
                   $this->getGraph()->toNQuads() . '.';
        } else {
            return $this->getSubject()->toNQuads() . ' ' .
                   $this->getPredicate()->toNQuads() . ' ' .
                   $this->getObject()->toNQuads() . '.';
        }
    }
}
