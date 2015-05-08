<?php

namespace Saft\Store\Result;

/**
 * Represents an empty result, usually after an INSERT or UPDATE SPARQL query.
 */
class EmptyResult extends Result
{
    /**
     */
    public function __construct()
    {
    }

    /**
     * @return boolean True
     */
    public function isEmptyResult()
    {
        return true;
    }

    /**
     * @return boolean True
     */
    public function isExceptionResult()
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
     * @return boolean False
     */
    public function isValueResult()
    {
        return false;
    }
}
