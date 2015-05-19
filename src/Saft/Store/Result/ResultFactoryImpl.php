<?php

namespace Saft\Store\Result;

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
     * @param  \Iterator|array|null $list optional
     * @return SetResult
     */
    public function createSetResult($list)
    {
        return new SetResultImpl($list);
    }

    /**
     * @param  \Iterator|array|null $list optional
     * @return SetResult
     */
    public function createStatementResult($list)
    {
        return new StatementSetResultImpl($list);
    }

    /**
     * @param mixed $scalar
     * @return ValueResult
     */
    public function createValueResult($scalar)
    {
        return new ValueResultImpl($scalar);
    }
}
