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
     * Transforms content of the Statement to n-quads form.
     *
     * @return string
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
                return $this->getSubject()->toNQuads() . ' ' .
                       $this->getPredicate()->toNQuads() . ' ' .
                       $this->getObject()->toNQuads() . ' .';
            }
        } else {
            throw new \Exception('A Statement has to be concrete in N-Quads.');
        }
    }

    /**
     * Transforms content of the Statement to n-triples form.
     *
     * @return string
     */
    public function toNTriples()
    {
        if ($this->isConcrete()) {
            return $this->getSubject()->toNQuads() . ' ' .
                   $this->getPredicate()->toNQuads() . ' ' .
                   $this->getObject()->toNQuads() . ' .';
        } else {
            throw new \Exception('A Statement has to be concrete in N-Triples.');
        }
    }

    public function __toString()
    {
        $string = sprintf("s: %s, p: %s, o: %s", $this->getSubject(), $this->getPredicate(), $this->getObject());
        if ($this->isQuad()) {
            $string .= ", g: " . $this->getGraph();
        }
        return $string;
    }

    public function equals(Statement $toTest)
    {
        if ($toTest instanceof Statement &&
            $this->getSubject()->equals($toTest->getSubject()) &&
            $this->getPredicate()->equals($toTest->getPredicate()) &&
            $this->getObject()->equals($toTest->getObject())
        ) {
            if ($this->isQuad() && $toTest->isQuad() && $this->getGraph()->equals($toTest->getGraph())) {
                return true;
            } elseif ($this->isTriple() && $toTest->isTriple()) {
                return true;
            }
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function matches(Statement $toTest)
    {
        if ($this->isConcrete() && $this->equals($toTest)) {
            return true;
        }

        if ($toTest instanceof Statement &&
            $this->getSubject()->matches($toTest->getSubject()) &&
            $this->getPredicate()->matches($toTest->getPredicate()) &&
            $this->getObject()->matches($toTest->getObject())
        ) {
            if ($this->isQuad() && $toTest->isQuad() && $this->getGraph()->matches($toTest->getGraph())) {
                return true;
            } elseif ($this->isQuad() && $this->getGraph()->isVariable()) {
                /*
                 * This case also matches the default graph i.e. if the graph is set to a variable it also matches the
                 * defaultgraph
                 */
                return true;
            } elseif ($this->isTriple() && $toTest->isTriple()) {
                return true;
            }
            /*
             * TODO What should happen if $this->isTriple() is true, should this pattern match any $quad?
             * This is the same descission, as, if the default graph should contain the union of all graphs!
             *
             * As I understand the situation with SPARQL it doesn't give a descission for this, but in the case that
             * named graphs are included in a query only using FROM NAMED the default graph is empty per definiton.
             * {@url http://www.w3.org/TR/2013/REC-sparql11-query-20130321/#rdfDataset}
             */
        }
        return false;
    }
}
