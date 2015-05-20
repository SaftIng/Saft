<?php

namespace Saft\Backend\EasyRdf\Test;

use Saft\Backend\EasyRdf\Data\ParserEasyRdf;
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
