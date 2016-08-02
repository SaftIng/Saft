<?php

namespace Saft\Rdf;

/**
 * The StatementIteratorFactory abstracts the creation of StatementIterators.
 *
 * @api
 * @package Saft\Rdf
 * @since 0.1
 */
interface StatementIteratorFactory
{
    /**
     * Creates a StatementIterator instance which uses an array to manage their entities.
     *
     * @param array|\Iterator $statements List of statements, represented by an array or instance which
     *                                    implements \Iterator interface.
     * @return StatementIterator
     * @api
     * @since 0.1
     */
    public function createStatementIteratorFromArray(array $statements);
}
