<?php

namespace Saft\Rdf;

interface StatementFactory
{
    /**
     * @param Node $subject
     * @param Node $predicate
     * @param Node $object
     * @param Node $graph optional
     * @return Statement
     */
    public function createStatement(Node $subject, Node $predicate, Node $object, Node $graph = null);
}
