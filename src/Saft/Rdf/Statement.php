<?php

namespace Saft\Rdf;

/**
 * This interface is common for RDF statement. It represents a 3-tuple and 4-tuple. A 3-tuple consists
 * of subject, predicate and object, whereas a 4-tuple is a 3-tuple but also contains a graph.
 *
 * @api
 * @package Saft\Rdf
 * @since 0.1
 */
interface Statement
{
    /**
     * Returns Statements subject.
     *
     * @return Node Subject node.
     * @api
     * @since 0.1
     */
    public function getSubject();

    /**
     * Returns Statements predicate.
     *
     * @return Node Predicate node.
     * @api
     * @since 0.1
     */
    public function getPredicate();

    /**
     * Returns Statements object.
     *
     * @return Node Object node.
     * @api
     * @since 0.1
     */
    public function getObject();

    /**
     * Returns Statements graph, if available.
     *
     * @return Node|null Graph node, if available.
     * @api
     * @since 0.1
     */
    public function getGraph();

    /**
     * If this statement consists of subject, predicate, object and graph, this function returns true,
     * false otherwise.
     *
     * @return boolean True, if this statement consists of subject, predicate, object and graph, false otherwise.
     * @api
     * @since 0.1
     */
    public function isQuad();

    /**
     * If this statement consists of subject, predicate and object, but no graph, this function returns true,
     * false otherwise.
     *
     * @return boolean True, if this statement consists of subject, predicate and object, but no graph, false otherwise.
     * @api
     * @since 0.1
     */
    public function isTriple();

    /**
     * Returns true if neither subject, predicate, object nor, if available, graph, are patterns.
     *
     * @return boolean True, if neither subject, predicate, object nor, if available, graph, are patterns,
     *                 false otherwise.
     * @api
     * @since 0.1
     */
    public function isConcrete();

    /**
     * Returns true if at least subject, predicate, object or, if available, graph, are patterns.
     *
     * @return boolean True, if at least subject, predicate, object or, if available, graph, are patterns,
     *                 false otherwise.
     * @api
     * @since 0.1
     */
    public function isPattern();

    /**
     * Get a valid NQuads serialization of the statement. If the statement is not concrete i.e. it contains variable
     * parts this method will throw an exception.
     *
     * @throws \Exception if the statment is not concrete
     * @return string a string representation of the statement in valid NQuads syntax.
     * @api
     * @since 0.1
     */
    public function toNQuads();

    /**
     * Get a string representation of the current statement. It should contain a human readable description of the parts
     * of the statement.
     *
     * @return string A string representation of the statement.
     * @api
     * @since 0.1
     */
    public function __toString();

    /**
     * Returns true, if the given argument matches the is statement-pattern.
     *
     * @param Statement $toCompare the statement to where this pattern shoul be applied to.
     * @api
     * @since 0.1
     */
    public function matches(Statement $toCompare);

    /**
     * Checks if a given Statement instance is equal to this instance.
     *
     * @param Statement $toCompare the statement to compare with
     * @return boolean True, if the given Statement instance is equal to this one.
     * @api
     * @since 0.1
     */
    public function equals(Statement $toCompare);
}
