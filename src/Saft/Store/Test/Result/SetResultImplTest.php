<?php

namespace Saft\Store\Test\Result;

use Saft\Store\Result\SetResultImpl;

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
