<?php

namespace Saft\Rdf;

/**
 * The StatementIterator interface extends the \Iterator interface by restricting it to Statements.
 *
 * @api
 * @package Saft\Rdf
 */
interface StatementIterator extends \Iterator
{
    /**
     * Get current Statement instance.
     *
     * @return Statement
     */
    public function current();

    /**
     * Get key of current Statement.
     *
     * @return scalar May not be meaningful, but must be distinct.
     */
    public function key();

    /**
     * Go to the next Statement instance. Any returned value is ignored.
     */
    public function next();

    /**
     * Reset this iterator. Be aware, it may not be implemented!
     */
    public function rewind();

    /**
     * Checks if the current Statement is valid.
     *
     * @return boolean
     */
    public function valid();
}
