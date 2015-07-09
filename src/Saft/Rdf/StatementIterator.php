<?php

namespace Saft\Rdf;

/**
 * The StatementIterator interface extends the \Iterator interface by restricting it to Statements.
 *
 * @api
 * @package Saft\Rdf
 * @since 0.1
 */
interface StatementIterator extends \Iterator
{
    /**
     * Get current Statement instance.
     *
     * @return Statement
     * @api
     * @since 0.1
     */
    public function current();

    /**
     * Get key of current Statement.
     *
     * @return scalar May not be meaningful, but must be distinct.
     * @api
     * @since 0.1
     */
    public function key();

    /**
     * Go to the next Statement instance. Any returned value is ignored.
     * @api
     * @since 0.1
     */
    public function next();

    /**
     * Reset this iterator. Be aware, it may not be implemented!
     * @api
     * @since 0.1
     */
    public function rewind();

    /**
     * Checks if the current Statement is valid.
     *
     * @return boolean
     * @api
     * @since 0.1
     */
    public function valid();
}
