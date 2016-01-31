<?php

namespace Saft\Addition\Redland\Test;

use Saft\Addition\Redland\Data\Parser;
use Saft\Data\Test\ParserAbstractTest;
use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\StatementFactoryImpl;

class ParserTest extends ParserAbstractTest
{
    /**
     * @return Parser
     */
    protected function newInstance($serialization)
    {
        return new Parser($serialization);
    }

    public function setUp()
    {
        if (!extension_loaded('redland')) {
            $this->markTestSkipped('Redland extension not loaded.');
        } else {
            parent::setUp();
        }
    }

    public function testConstructor()
    {
        $this->setExpectedException('Exception');

        new Parser('invalid');
    }

    public function testParseStreamToIteratorTurtleFile()
    {
        $this->markTestSkipped('Redland quits turtle file parsing with exception.');
    }

    public function testParseStreamToIteratorTurtleString()
    {
        $this->markTestSkipped('Redland quits turtle string parsing with exception.');
    }

    public function testParseStringToIteratorTurtleFile()
    {
        $this->markTestSkipped('Redland quits turtle file parsing with exception.');
    }

    public function testParseStringToIteratorTurtleString()
    {
        $this->markTestSkipped('Redland quits turtle string parsing with exception.');
    }
}
