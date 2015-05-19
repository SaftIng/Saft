<?php

namespace Saft\Rdf;

interface StatementIterator extends \Iterator
{
    /**
     * @return Statement
     */
    public function current();

    /**
     * @return scalar May not be meaningful, but must be distinct.
     */
    public function key();

    /**
     * Any returned value is ignored.
     */
    public function next();

    /**
     * Reset the iterator. Be aware, it may not be implemented!
     */
    public function rewind();

    /**
     * @return boolean
     */
    public function valid();
}
