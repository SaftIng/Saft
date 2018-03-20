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

class ResultFactoryImpl implements ResultFactory
{
    /**
     * @return EmptyResult
     */
    public function createEmptyResult()
    {
        return new EmptyResultImpl();
    }

    /**
     * @param \Iterator|array|null $list optional, default is array()
     *
     * @return SetResult
     */
    public function createSetResult($list = [])
    {
        return new SetResultImpl($list);
    }

    /**
     * @param \Iterator|array|null $list optional, default is array()
     *
     * @return SetResult
     */
    public function createStatementResult($list = [])
    {
        return new StatementSetResultImpl($list);
    }

    /**
     * @param mixed $scalar
     *
     * @return ValueResult
     */
    public function createValueResult($scalar)
    {
        return new ValueResultImpl($scalar);
    }
}
