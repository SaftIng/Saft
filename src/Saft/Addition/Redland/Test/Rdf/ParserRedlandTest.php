<?php

namespace Saft\Addition\Redland\Test\Rdf;

use Saft\Addition\Redland\Data\Parser;
use Saft\Data\Test\ParserAbstractTest;

class ParserRedlandTest extends ParserAbstractTest
{
    /**
     * @return Parser
     */
    protected function newInstance()
    {
        return new Parser();
    }
}
