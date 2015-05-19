<?php

namespace Saft\Store\Result;

interface ResultFactory
{
    /**
     * @return Result
     */
    public function createEmptyResult();

    /**
     * @param  \Iterator|array|null $list optional
     * @return SetResult
     */
    public function createSetResult($list);

    /**
     * @param  \Iterator|array|null $list optional
     * @return SetResult
     */
    public function createStatementResult($list);

    /**
     * @param  mixed $scalar
     * @return ValueResult
     */
    public function createValueResult($scalar);
}
