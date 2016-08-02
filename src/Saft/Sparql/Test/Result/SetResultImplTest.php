<?php

namespace Saft\Sparql\Test\Result;

use Saft\Sparql\Result\SetResultImpl;

class SetResultImplTest extends SetResultAbstractTest
{
    /**
     * @param \Iterator $list
     * @return SetResult
     */
    public function newInstance($list)
    {
        return new SetResultImpl($list);
    }
}
