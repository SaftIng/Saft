<?php

namespace Saft\Rdf;

class ArrayStatementIteratorImpl implements StatementIterator
{
    /**
     * @var \ArrayIterator over the statements array
     */
    protected $arrayIterator;

    /**
     * @param array $statements array of instances of Statement
     * @throws \Exception If $statements does contain at least one non-Statement instance.
     */
    public function __construct(array $statements)
    {
        // check that each entry of the array is of type Statement
        foreach ($statements as $statement) {
            if (false === $statement instanceof Statement) {
                throw new \Exception('Parameter $statements must contain Statement instances.');
            }
        }

        $this->arrayIterator = new \ArrayIterator($statements);
    }

    /**
     * @return Statement
     */
    public function current()
    {
        return $this->arrayIterator->current();
    }

    /**
     * @return int position in the statements array
     */
    public function key()
    {
        return $this->arrayIterator->key();
    }

    /**
     * Any returned value is ignored.
     */
    public function next()
    {
        $this->arrayIterator->next();
    }

    /**
     * @return boolean
     */
    public function valid()
    {
        return $this->arrayIterator->valid();
    }

    /**
     *
     */
    public function rewind()
    {
        $this->arrayIterator->rewind();
    }
}
