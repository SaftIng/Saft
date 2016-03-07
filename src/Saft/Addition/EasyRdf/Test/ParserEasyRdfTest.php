<?php

namespace Saft\Addition\EasyRdf\Test;

use Saft\Addition\EasyRdf\Data\ParserEasyRdf;
use Saft\Addition\EasyRdf\Data\ParserFactoryEasyRdf;
use Saft\Data\Test\ParserAbstractTest;
use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\StatementFactoryImpl;

class ParserEasyRdfTest extends ParserAbstractTest
{
    private $factory;

    public function __construct()
    {
        $this->factory = new ParserFactoryEasyRdf(new NodeFactoryImpl(), new StatementFactoryImpl());
    }

    /**
     * @return Parser
     */
    protected function newInstance($serialization)
    {
        return $this->factory->createParserFor($serialization);
    }
}
