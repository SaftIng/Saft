<?php

namespace Saft\Rdf;

class ArrayStatementIteratorImpl extends AbstractStatementIterator
{

    /**
     * @var \ArrayIterator over the statements array
     */
    protected $arrayIterator;

    /**
     * @param array $statements array of instances of Statement
     */
    public function __construct(array $statements)
    {
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
     * Implement rewind, because we can
     */
    public function rewind()
    {
        $this->arrayIterator->rewind();
    }
}
