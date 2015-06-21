<?php

namespace Saft\Sparql\Test\Result;

use Saft\Sparql\Result\ValueResultImpl;

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
