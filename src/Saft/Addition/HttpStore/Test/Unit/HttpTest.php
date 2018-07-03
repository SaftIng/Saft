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

namespace Saft\Addition\HttpStore\Test\Unit;

use Curl\Curl;
use Saft\Addition\HttpStore\Store\HttpStore;
use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\RdfHelpers;
use Saft\Rdf\StatementFactoryImpl;
use Saft\Rdf\StatementIteratorFactoryImpl;
use Saft\Rdf\Test\TestCase;
use Saft\Sparql\Result\ResultFactoryImpl;
use Symfony\Component\Yaml\Yaml;

class HttpTest extends TestCase
{
    public function setUp()
    {
        global $config;

        parent::setUp();

        // init fixture
        $this->fixture = new HttpStore(
            new NodeFactoryImpl(),
            new StatementFactoryImpl(),
            new ResultFactoryImpl(),
            new StatementIteratorFactoryImpl(),
            new RdfHelpers(),
            $config
        );
        $this->fixture->setClient(new Curl());
    }

    public function testGetQueryTypeConstruct()
    {
        $query = 'PREFIX foaf: <http://xmlns.com/foaf/0.1/>
PREFIX vcard:   <http://www.w3.org/2001/vcard-rdf/3.0#>
CONSTRUCT   { <http://example.org/person#Alice> vcard:FN ?name }
WHERE       { ?x foaf:name ?name }';
        $this->assertEquals('construct', $this->fixture->getQueryType($query));
    }

    public function testGetQueryTypeSelect()
    {
        $query = 'PREFIX foaf:    <http://xmlns.com/foaf/0.1/>
SELECT ?nameX ?nameY ?nickY
WHERE
  { ?x foaf:knows ?y ;
       foaf:name ?nameX .
    ?y foaf:name ?nameY .
    OPTIONAL { ?y foaf:nick ?nickY }
  }';
        $this->assertEquals('select', $this->fixture->getQueryType($query));
    }

    public function testGetQueryTypeUpdate()
    {
        // INSERT DATA
        $query = 'PREFIX dc: <http://purl.org/dc/elements/1.1/>
INSERT DATA
{
  <http://example/book1> dc:title "A new book" ;
                         dc:creator "A.N.Other" .
}';
        $this->assertEquals('insert-data', $this->fixture->getQueryType($query));

        // DELETE DATA
        $query = 'PREFIX dc: <http://purl.org/dc/elements/1.1/>

DELETE DATA
{
  <http://example/book2> dc:title "David Copperfield" ;
                         dc:creator "Edmund Wells" .
}';
        $this->assertEquals('delete-data', $this->fixture->getQueryType($query));
    }
}
