<?php

namespace Saft\Rdf;

/**
 * The StatementFactory interface abstracts the creating of new instances of RDF statements by hiding different
 * implementation details.
 *
 * @api
 * @package Saft\Rdf
 */
interface StatementFactory
{
    /**
     * Creates a new statement, either a 3-tuple or 4-tuple.
     *
     * @param Node $subject Subject of the statement.
     * @param Node $predicate Predicate of the statement.
     * @param Node $object Object of the statement.
     * @param Node $graph optional Graph of the statement. (optional)
     * @return Statement Instance of Statement.
     */
    public function createStatement(Node $subject, Node $predicate, Node $object, Node $graph = null);
}
