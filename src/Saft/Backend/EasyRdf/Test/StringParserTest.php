<?php

namespace Saft\Backend\EasyRdf\Test;

use Saft\Backend\EasyRdf\Data\StringParser;
use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\StatementFactoryImpl;
use Saft\Data\Test\ParserAbstractTest;

class StringParserTest extends ParserAbstractTest
{
    public function getParserInstance()
    {
        return new StringParser(new NodeFactoryImpl(), new StatementFactoryImpl());
    }
}
