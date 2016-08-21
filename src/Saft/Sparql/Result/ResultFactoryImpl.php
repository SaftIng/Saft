<?php

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
     * @param  \Iterator|array|null $list optional, default is array()
     * @return SetResult
     */
    public function createSetResult($list = array())
    {
        return new SetResultImpl($list);
    }

    /**
     * @param  \Iterator|array|null $list optional, default is array()
     * @return SetResult
     */
    public function createStatementResult($list = array())
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
