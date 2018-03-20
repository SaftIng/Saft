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
 * The StatementIteratorFactory abstracts the creation of StatementIterators.
 *
 * @api
 *
 * @since 0.1
 */
interface StatementIteratorFactory
{
    /**
     * Creates a StatementIterator instance which uses an array to manage their entities.
     *
     * @param array|\Iterator $statements list of statements, represented by an array or instance which
     *                                    implements \Iterator interface
     *
     * @return StatementIterator
     *
     * @api
     *
     * @since 0.1
     */
    public function createStatementIteratorFromArray(array $statements);
}
