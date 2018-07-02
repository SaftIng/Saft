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
    protected $variables = [];

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
        parent::__construct($array, $flags);

        // check that each entry of $array is an array to
        foreach ($array as $entry) {
            // is entry an array?
            if (\false === \is_array($entry)) {
                throw new \Exception('Parameter $array must only contain arrays.');
            }
            // does the array only contains entries of type Node?
            // TODO remove that, if performance drops too heavily. also, this makes it impossible to use
            //      chunk-based reading, because we load everything in the memory anyway.
            foreach ($entry as $key => $value) {
                if (false === $value instanceof Node) {
                    throw new \Exception('$array contains entries which are not of type Saft/Rdf/Node.');
                }
            }
        }
    }

    /**
     * @return array
     */
    public function getVariables(): array
    {
        return $this->variables;
    }

    /**
     * @return bool True
     */
    public function isEmptyResult(): bool
    {
        return false;
    }

    /**
     * @return bool True
     */
    public function isSetResult(): bool
    {
        return true;
    }

    /**
     * @return bool False
     */
    public function isStatementSetResult(): bool
    {
        return false;
    }

    /**
     * @return bool True
     */
    public function isValueResult(): bool
    {
        return false;
    }

    /**
     * @param array $variables
     */
    public function setVariables(array $variables)
    {
        $this->variables = $variables;
    }

    /**
     * @return array
     *
     * @api
     *
     * @since 2.0.0
     */
    public function toArray(): array
    {

    }
}
