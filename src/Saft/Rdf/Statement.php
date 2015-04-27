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
     * Get a valid NQuads serialization of the statement. If the statement is not concrete i.e. it contains variable
     * parts this method will throw an exception.
     *
     * @throws \Exception if the statment is not concrete
     * @return string a string representation of the statement in valid NQuads syntax.
     */
    public function toNQuads();

    /**
     * Get a string representation of the current statement. It should contain a human readable description of the parts
     * of the statement.
     *
     * @return string a string representation of the statement
     */
    public function __toString();

    /**
     * Returns true, if this matches the given pattern. This have to be concrete.
     *
     * @param  Statement       $pattern
     * @throws \LogicException If isConcrete() returns false.
     */
    public function matches(Statement $pattern);
}
