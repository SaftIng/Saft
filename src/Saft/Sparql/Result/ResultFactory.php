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

interface ResultFactory
{
    /**
     * @return Result
     */
    public function createEmptyResult(): EmptyResult;

    /**
     * @param \Iterator|array|null $list optional
     *
     * @return SetResult
     */
    public function createSetResult($list = []): SetResult;

    /**
     * @param \Iterator|array|null $list optional
     *
     * @return StatementResult
     */
    public function createStatementResult($list = []): StatementResult;

    /**
     * @param mixed $scalar
     *
     * @return ValueResult
     */
    public function createValueResult($scalar): ValueResult;
}
