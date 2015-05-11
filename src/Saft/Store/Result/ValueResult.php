<?php

namespace Saft\Store\Result;

class ValueResult extends Result
{
    /**
     * @param mixed $scalar
     */
    public function __construct($scalar)
    {
        $this->setResultObject($scalar);
    }

    /**
     * @return boolean True
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
    public function isStatementResult()
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

    /**
     * @param mixed
     */
    public function setResultObject($resultObject)
    {
        if (true === is_scalar($resultObject)) {
            parent::setResultObject($resultObject);

        } else {
            throw new \Exception('Parameter $resultObject must be a scalar.');
        }
    }
}
