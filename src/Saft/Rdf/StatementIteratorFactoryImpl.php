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

class StatementIteratorFactoryImpl implements StatementIteratorFactory
{
    /**
     * Creates a StatementIterator instance which uses an array to manage their entities.
     *
     * @param array|\Iterator $statements list of statements, represented by an array or instance
     *                                    which implements \Iterator interface
     *
     * @return StatementIterator
     */
    public function createStatementIteratorFromArray(array $statements)
    {
        return new ArrayStatementIteratorImpl($statements);
    }
}
