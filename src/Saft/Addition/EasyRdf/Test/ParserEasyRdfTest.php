<?php

namespace Saft\Addition\EasyRdf\Test;

use Saft\Addition\EasyRdf\Data\ParserEasyRdf;
use Saft\Data\Test\ParserAbstractTest;
use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\StatementFactoryImpl;

class ParserEasyRdfTest extends ParserAbstractTest
{
    /**
     * @return Parser
     */
    protected function newInstance()
    {
        return new ParserEasyRdf(new NodeFactoryImpl(), new StatementFactoryImpl());
    }
}
