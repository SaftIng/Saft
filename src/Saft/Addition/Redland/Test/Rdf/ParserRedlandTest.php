<?php

namespace Saft\Addition\EasyRdf\Test;

use Saft\Addition\Redland\Data\Parser;
use Saft\Data\Test\ParserAbstractTest;
use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\StatementFactoryImpl;

class ParserRedlandTest extends ParserAbstractTest
{
    /**
     * @return Parser
     */
    protected function newInstance()
    {
        return new Parser(new NodeFactoryImpl(), new StatementFactoryImpl());
    }
}
?>
