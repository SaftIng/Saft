<?php

namespace Saft\Rdf;

class StatementIteratorFactoryImpl implements StatementIteratorFactory
{
    /**
     * Creates a StatementIterator instance which uses an array to manage their entities.
     *
     * @param  array|\Iterator   $statements List of statements, represented by an array or instance
     *                                       which implements \Iterator interface.
     * @return StatementIterator
     */
    public function createArrayStatementIterator($statements)
    {
        if (is_array($statements)) {
            return new ArrayStatementIteratorImpl($statements);

        } elseif ($statements instanceof \Iterator) {
            $arrayIterator = new \ArrayIterator($statements);
            return new ArrayStatementIteratorImpl($arrayIterator->getArrayCopy());

        } else {
            throw new \Exception('Parameter $statements is neither an array nor instace of \Iterator.');
        }
    }
}
