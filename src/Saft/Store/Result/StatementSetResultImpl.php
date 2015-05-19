<?php

namespace Saft\Store\Result;

use Saft\Rdf\Statement;

/**
 * This class is a certain kind of SetResult, it only contains Statements.
 */
class StatementSetResultImpl extends SetResultImpl
{
    /**
     * Constructor.
     *
     * @param  object|array              $array The array or object to be iterated on.
     * @param  int                       $flags Flags to control the behaviour of the ArrayIterator object.
     *                                          See ArrayIterator::setFlags for more information:
     *                                          http://php.net/manual/de/arrayiterator.setflags.php
     * @throws \InvalidArgumentException If anything besides an array or an object is given.
     */
    public function __construct($array = array(), $flags = 0)
    {
        // need this construction to be able to call the constructor of the parent class of SetResultImpl
        $parentClass = new \ReflectionClass($this);
        $parentClass = $parentClass->getParentClass()->getParentClass()->getName();
        $parentClass::__construct($array, $flags);

        // check that each entry of $array is an array to
        foreach ($array as $entry) {
            if (false === $entry instanceof Statement) {
                throw new \Exception('Parameter $array must only contain Statement instances.');
            }
        }
    }

    /**
     * @return boolean False
     */
    public function isSetResult()
    {
        return false;
    }

    /**
     * @return boolean True
     */
    public function isStatementSetResult()
    {
        return true;
    }
}
