<?php

namespace Saft\Store\Test\Result;

use Saft\Rdf\StatementImpl;
use Saft\Rdf\AnyPatternImpl;
use Saft\Store\Result\StatementSetResultImpl;
use Saft\Test\TestCase;

class StatementSetResultImplTest extends StatementSetResultAbstractTest
{
    /**
     * @return StatementResult
     */
    public function newInstance($list)
    {
        return new StatementSetResultImpl($list);
    }
}
