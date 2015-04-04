<?php

namespace Saft\Rdf;

interface Statement
{
    /**
     * Return Statements subject.
     * @return NamedNode|BlankNode
     */
    public function getSubject();

    /**
     * Return Statements predicate
     * @return NamedNode
     */
    public function getPredicate();

    /**
     * Return Statements object.
     * @return Node
     */
    public function getObject();

    /**
     * @return NamedNode|null
     */
    public function getGraph();

    /**
     * @return boolean
     */
    public function isQuad();
    
    /**
     * @return boolean
     */
    public function isTriple();

    /**
     * Returns true if subject, predicate and object are not variables, i. e.
     * subject != ? AND predicate != ? AND object != ?.
     * @return boolean
     */
    public function isConcrete();

    /**
     * @return boolean
     */
    public function isPattern();

    /**
     * @return boolean
     */
    public function toNQuads();

    /**
     * @return boolean
     */
    public function toSparqlFormat();

    /**
     * Returns true, if this matches the given pattern. This
     * have to be concrete.
     * @param Statement $pattern
     * @throws \LogicException when !$this->isConcrete()
     */
    public function matches(Statement $pattern);
}
