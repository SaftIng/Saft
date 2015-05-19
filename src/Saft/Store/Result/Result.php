<?php

namespace Saft\Store\Result;

/**
 * This class represents the result of a store operation, usually a SPARQL query.
 */
interface Result
{
    /**
     * @return boolean True, if this instance represents an empty result.
     */
    public function isEmptyResult();

    /**
     * @return boolean True, if this instance represents a set result, which is a list of associative arrays.
     */
    public function isSetResult();

    /**
     * @return boolean True, if this instance represents a statement set result, which is a list of statements.
     */
    public function isStatementSetResult();

    /**
     * @return boolean True, if this instance is a ValueResult
     */
    public function isValueResult();
}
