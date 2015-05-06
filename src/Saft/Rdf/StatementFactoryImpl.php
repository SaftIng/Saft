<?php

namespace Saft\Rdf;

class StatementFactoryImpl implements StatementFactory
{
    public function createStatement(Node $subject, Node $predicate, Node $object, Node $graph = null)
    {
        return new StatementImpl($subject, $predicate, $object, $graph);
    }
}
