<?php
namespace Saft\Rdf;

class AbstractStatement implements Statement
{
    /**
     * @return boolean
     */
    public function isConcrete()
    {
        if ($this->isQuad() && !$this->graph->isConcrete()) {
                return false;
        }

        return $this->subject->isConcrete()
               && $this->predicate->isConcrete()
               && $this->object->isConcrete();
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
        return $this->getSubject()->toNT() . ' ' .
               $this->getPredicate()->toNT() . ' ' .
               $this->getObject()->toNT() . ' ' .
               $this->getGraph()->toNT() . '.';
    }

    /**
     * @return boolean
     */
    public function toSparqlFormat()
    {
        return $this->getSubject()->toNT() .' '.
               $this->getPredicate()->toNT() .' '.
               $this->getObject()->toNT() .'.';
    }
}
