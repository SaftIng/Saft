<?php

namespace Saft\Store\Result;

/**
 * This class represents a result set. Each entry is an associative array with binding + according Node.
 */
class SetResultImpl extends \ArrayIterator implements SetResult
{
    /**
     * Contains a list of variable names which were used in the SPARQL which let to this result.
     *
     * @var array
     */
    protected $variables = array();

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
        parent::__construct($array, $flags);

        // check that each entry of $array is an array to
        foreach ($array as $entry) {
            if (false === is_array($entry)) {
                throw new \Exception('Parameter $array must only contain arrays.');
            }
        }
    }

    /**
     * @return array
     */
    public function getVariables()
    {
        return $this->variables;
    }

    /**
     * @return boolean True
     */
    public function isEmptyResult()
    {
        return false;
    }

    /**
     * @return boolean True
     */
    public function isSetResult()
    {
        return true;
    }

    /**
     * @return boolean False
     */
    public function isStatementSetResult()
    {
        return false;
    }

    /**
     * @return boolean True
     */
    public function isValueResult()
    {
        return false;
    }

    /**
     * @param array $variables
     */
    public function setVariables($variables)
    {
        $this->variables = $variables;
    }
}
