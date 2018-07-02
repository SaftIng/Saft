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
 * This class represents the result of a store operation, usually a SPARQL query.
 */
interface Result
{
    /**
     * @return bool true, if this instance represents an empty result
     */
    public function isEmptyResult(): bool;

    /**
     * @return bool true, if this instance represents a set result, which is a list of associative arrays
     */
    public function isSetResult(): bool;

    /**
     * @return bool true, if this instance represents a statement set result, which is a list of statements
     */
    public function isStatementSetResult(): bool;

    /**
     * @return bool True, if this instance is a ValueResult
     */
    public function isValueResult(): bool;
}
