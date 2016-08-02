<?php

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
     * @return boolean False
     */
    public function isEmptyResult()
    {
        return false;
    }

    /**
     * @return boolean False
     */
    public function isSetResult()
    {
        return false;
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
        return true;
    }
}
