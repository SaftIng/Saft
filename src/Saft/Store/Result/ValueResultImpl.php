<?php

namespace Saft\Store\Result;

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
        if (true === is_scalar($value)) {
            $this->value = $value;

        } else {
            throw new \Exception('Parameter $value must be a scalar.');
        }
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
