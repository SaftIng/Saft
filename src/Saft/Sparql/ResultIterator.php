<?php
namespace Saft\Sparql;

interface ResultIterator extends \Iterator
{
    /**
     * @return ResultBinding
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

    /**
     * @return array keys of the ResultBinding instances
     */
    public function getVariables();
}
