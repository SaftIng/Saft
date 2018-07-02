<?php

/*
 * This file is part of Saft.
 *
 * (c) Konrad Abicht <hi@inspirito.de>
 * (c) Natanael Arndt <arndt@informatik.uni-leipzig.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Saft\Data\Test\Parser;

use Saft\Data\Parser;
use Saft\Data\Parser\RDFXMLParser;
use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\RdfHelpers;
use Saft\Rdf\StatementFactoryImpl;
use Saft\Rdf\StatementIteratorFactoryImpl;

class RDFXMLParserTest extends AbstractRDFXMLParserTest
{
    protected function getInstance(): Parser
    {
        return new RDFXMLParser(
            new NodeFactoryImpl(),
            new StatementFactoryImpl(),
            new StatementIteratorFactoryImpl(),
            new RdfHelpers()
        );
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Not implemented yet.
     */
    public function testGetCurrentPrefixList()
    {
        $this->fixture->getCurrentPrefixList();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage No base URI support for now. To continue, just leave $baseUri = null.
     */
    public function testParseStringToIteratorNoBaseUriSupport()
    {
        $result = $this->fixture->parseStringToIterator('123', 'http://foo');
    }
}
