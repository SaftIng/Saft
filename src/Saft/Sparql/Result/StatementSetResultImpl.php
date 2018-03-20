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

namespace Saft\Sparql\Result;

use Saft\Rdf\Statement;
use Saft\Rdf\StatementIterator;

/**
 * This class is a certain kind of SetResult, it only contains Statements.
 */
class StatementSetResultImpl extends SetResultImpl implements StatementIterator
{
    /**
     * Constructor.
     *
     * @param object|array $array the array or object to be iterated on
     * @param int          $flags Flags to control the behaviour of the ArrayIterator object.
     *                            See ArrayIterator::setFlags for more information:
     *                            http://php.net/manual/de/arrayiterator.setflags.php
     *
     * @throws \InvalidArgumentException if anything besides an array or an object is given
     */
    public function __construct($array = [], $flags = 0)
    {
        // need this construction to be able to call the constructor of the parent class of SetResultImpl
        $parentClass = new \ReflectionClass($this);
        $parentClass = $parentClass->getParentClass()->getParentClass()->getName();
        $parentClass::__construct($array, $flags);

        // check that each entry of $array is a Statement
        foreach ($array as $entry) {
            if (false === $entry instanceof Statement) {
                throw new \Exception('Parameter $array must only contain Statement instances.');
            }
        }
    }

    /**
     * @return bool False
     */
    public function isEmptyResult()
    {
        return false;
    }

    /**
     * @return bool False
     */
    public function isSetResult()
    {
        return false;
    }

    /**
     * @return bool True
     */
    public function isStatementSetResult()
    {
        return true;
    }

    /**
     * @return bool False
     */
    public function isValueResult()
    {
        return false;
    }
}
