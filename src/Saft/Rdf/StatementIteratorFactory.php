<?php

namespace Saft\Rdf;

interface StatementIteratorFactory
{
    /**
     * Creates a StatementIterator instance which uses an array to manage their entities.
     *
     * @param  array|\Iterator   $statements List of statements, represented by an array or instance
     *                                       which implements \Iterator interface.
     * @return StatementIterator
     *
     * TODO should become createStatementIteratorFromArray
     */
    public function createIteratorFromArray(array $statements);
}
