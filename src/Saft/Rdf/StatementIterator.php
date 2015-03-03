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
     * May not be implemented
     */
    public function rewind();

    /**
     * @return boolean
     */
    public function valid();
}
