<?php

namespace Saft\Sparql\Test\Result;

use Saft\Rdf\AnyPatternImpl;
use Saft\Rdf\StatementImpl;
use Saft\Sparql\Result\StatementSetResultImpl;
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
