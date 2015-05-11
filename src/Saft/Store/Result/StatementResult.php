<?php

namespace Saft\Store\Result;

use Saft\Rdf\Statement;

/**
 * This class is a certain kind of SetResult, it only contains Statements.
 */
class StatementResult extends SetResult
{
    /**
     * @param Statement $statement
     */
    public function append($statement)
    {
        if ($statement instanceof Statement) {
            parent::append($statement);

        } else {
            throw new \Exception('Its not allowed to append non-Statement instances.');
        }
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
     * @return boolean True
     */
    public function isStatementResult()
    {
        return true;
    }

    /**
     * @return boolean True
     */
    public function isValueResult()
    {
        return false;
    }
}
