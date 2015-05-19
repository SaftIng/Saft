<?php

namespace Saft\Store\Test\Result;

use Saft\Store\Result\ValueResultImpl;

class ValueResultImplTest extends ValueResultAbstractTest
{
    /**
     * @param  $mixed $scalar
     * @return Result
     */
    public function newInstance($scalar)
    {
        return new ValueResultImpl($scalar);
    }
}
