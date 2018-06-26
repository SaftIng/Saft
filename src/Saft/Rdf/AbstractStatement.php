<?php

/*
 * This file is part of Saft.
 *
 * (c) Konrad Abicht <hi@inspirito.de>
 * (c) Natanael Arndt <arndt@informatik.uni-leipzig.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Saft\Rdf;

/**
 * @api
 *
 * @since 0.1
 */
abstract class AbstractStatement implements Statement
{
    /**
     * Returns true if neither subject, predicate, object nor, if available, graph, are patterns.
     *
     * @return bool true, if if neither subject, predicate, object nor, if available, graph, are patterns,
     *              false otherwise
     *
     * @api
     *
     * @since 0.1
     */
    public function isConcrete(): bool
    {
        if ($this->isQuad() && !$this->getGraph()->isConcrete()) {
            return false;
        }

        return $this->getSubject()->isConcrete()
               && $this->getPredicate()->isConcrete()
               && $this->getObject()->isConcrete();
    }

    /**
     * Returns true if at least subject, predicate, object or, if available, graph, are patterns.
     *
     * @return bool true, if at least subject, predicate, object or, if available, graph, are patterns,
     *              false otherwise
     *
     * @api
     *
     * @since 0.1
     */
    public function isPattern(): bool
    {
        return false === $this->isConcrete();
    }

    /**
     * Transforms content of the Statement to n-quads form.
     *
     * @return string N-Quads string containing subject, predicate, object and graph, if available
     *
     * @throws \Exception if this instance is a non-concrete statement
     *
     * @api
     *
     * @since 0.1
     */
    public function toNQuads(): string
    {
        if ($this->isConcrete()) {
            if ($this->isQuad()) {
                return $this->getSubject()->toNQuads().' '.
                       $this->getPredicate()->toNQuads().' '.
                       $this->getObject()->toNQuads().' '.
                       $this->getGraph()->toNQuads().' .';
            } else {
                return $this->getSubject()->toNQuads().' '.
                       $this->getPredicate()->toNQuads().' '.
                       $this->getObject()->toNQuads().' .';
            }
        } else {
            throw new \Exception('A Statement has to be concrete in N-Quads.');
        }
    }

    /**
     * Transforms content of the Statement to n-triples form.
     *
     * @return string N-triples string, containing subject, predicate and object
     *
     * @throws \Exception if this instance is a non-concrete statement
     *
     * @api
     *
     * @since 0.1
     */
    public function toNTriples(): string
    {
        if ($this->isConcrete()) {
            return $this->getSubject()->toNQuads().' '.
                   $this->getPredicate()->toNQuads().' '.
                   $this->getObject()->toNQuads().' .';
        } else {
            throw new \Exception('A Statement has to be concrete in N-Triples.');
        }
    }

    /**
     * This method is ment for getting some kind of human readable string representation of the current node.
     * It returns a string which contains subject, predicate and object.
     *
     * @return string formated string which contains subject, predicate and object
     *
     * @api
     *
     * @since 0.1
     */
    public function __toString(): string
    {
        $string = sprintf('s: %s, p: %s, o: %s', $this->getSubject(), $this->getPredicate(), $this->getObject());
        if ($this->isQuad()) {
            $string .= ', g: '.$this->getGraph();
        }

        return $string;
    }

    /**
     * Checks if a given statement is equal to this instance.
     *
     * @param Statement $toTest statement to check this instance against
     *
     * @return bool true, if this instance is equal to the given instance, false otherwise
     *
     * @api
     *
     * @since 0.1
     */
    public function equals(Statement $toTest): bool
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
     * Checks if this instance matches a given instance.
     *
     * @param Statement $toTest statement instance to check for a match
     *
     * @return bool true, if this instance matches a given Statement instance, false otherwise
     *
     * @api
     *
     * @since 0.1
     */
    public function matches(Statement $toTest): bool
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
            } elseif ($this->isQuad() && $this->getGraph()->isPattern()) {
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
