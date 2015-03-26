<?php

namespace Saft\Store\Test;

class AbstractTriplePatternStoreUnitTest extends \PHPUnit_Framework_TestCase
{

    protected function setUp()
    {
        $this->store = $this->getMockForAbstractClass(
            '\Saft\Store\AbstractTriplePatternStore'
        );
    }

    public function testCreateStatement()
    {
        /*$statement1 = new \Saft\Rdf\TripleNEW('a1', 'b1', 'c1');
        $statement2 = new \Saft\Rdf\QuadNEW('a2', 'b2', 'c2', 'd2');

        $statements = array($statement1, $statement2);

        return $statements;*/
    }

    public function tearDown()
    {
        unset($this->store);
    }
}
