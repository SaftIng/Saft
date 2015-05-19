<?php

namespace Saft\Store\Result;

/**
 * Represents an empty result, usually after an INSERT or UPDATE SPARQL query.
 */
class EmptyResultImpl implements Result
{
    /**
     * @return boolean True
     */
    public function isEmptyResult()
    {
        return true;
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
     * @return boolean False
     */
    public function isValueResult()
    {
        return false;
    }
}
