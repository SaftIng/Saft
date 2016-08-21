<?php

namespace Saft\Sparql\Test\Query;

use Saft\Sparql\Query\QueryUtils;
use Saft\Test\TestCase;

class QueryUtilsTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->fixture = new QueryUtils();
    }

    /*
     * Tests for getQueryType
     */

    public function testGetQueryTypeAsk()
    {
        $query = 'PREFIX foaf:    <http://xmlns.com/foaf/0.1/>
            PREFIX vcard:   <http://www.w3.org/2001/vcard-rdf/3.0#>
            ASK
            WHERE       { ?x foaf:name ?name }';

        $this->assertEquals('askQuery', $this->fixture->getQueryType($query));
    }

    public function testGetQueryTypeClearGraph()
    {
        $query = 'CLEAR GRAPH <';

        $this->assertEquals('graphQuery', $this->fixture->getQueryType($query));
    }

    public function testGetQueryTypeConstruct()
    {
        $query = 'PREFIX foaf:    <http://xmlns.com/foaf/0.1/>
            PREFIX vcard:   <http://www.w3.org/2001/vcard-rdf/3.0#>
            CONSTRUCT   { <http://example.org/person#Alice> vcard:FN ?name }
            WHERE       { ?x foaf:name ?name }';

        $this->assertEquals('constructQuery', $this->fixture->getQueryType($query));
    }

    public function testGetQueryTypeDescribe()
    {
        $query = 'PREFIX foaf:   <http://xmlns.com/foaf/0.1/>
            DESCRIBE ?x ?y <http://example.org/>
            WHERE    {?x foaf:knows ?y}';

        $this->assertEquals('describeQuery', $this->fixture->getQueryType($query));
    }

    public function testGetQueryTypeDropGraph()
    {
        $query = 'DROP Graph <';

        $this->assertEquals('graphQuery', $this->fixture->getQueryType($query));
    }

    public function testGetQueryTypeInsertInto()
    {
        $query = 'INSERT INTO <';

        $this->assertEquals('updateQuery', $this->fixture->getQueryType($query));
    }

    public function testGetQueryTypeSelect()
    {
        $query = 'PREFIX foaf: <http://xmlns.com/foaf/0.1/>
            PREFIX vcard:<http://www.w3.org/2001/vcard-rdf/3.0#>
            Select ?s ?p ?o
            WHERE { ?x foaf:name ?name }';

        $this->assertEquals('selectQuery', $this->fixture->getQueryType($query));
    }
}
