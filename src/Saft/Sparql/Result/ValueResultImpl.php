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

class ValueResultImpl implements ValueResult
{
    /**
     * @var scalar
     */
    protected $value;

    /**
     * @param mixed $value Value of type scalar: int, string or float
     */
    public function __construct($value)
    {
        if (!is_scalar($value)) {
            throw new \Exception('The fist argument of the constructor has to be a scalar.');
        }

        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
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
     * @return bool False
     */
    public function isStatementSetResult()
    {
        return false;
    }

    /**
     * @return bool True
     */
    public function isValueResult()
    {
        return true;
    }
}
