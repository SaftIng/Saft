<?php

namespace Saft\Rdf\Test;

use Saft\Rdf\StatementIteratorFactory;
use Saft\Test\TestCase;

abstract class StatementIteratorFactoryAbstractTest extends TestCase
{
    /**
     * @return StatementIteratorFactory
     */
    abstract public function newInstance();

    /*
     * Tests createStatementIteratorFromArray
     */

    public function testCreateStatementIteratorFromArrayArrayGiven()
    {
        $this->fixture = $this->newInstance();
        $parameter = array();

        $this->assertClassOfInstanceImplements(
            $this->fixture->createStatementIteratorFromArray($parameter),
            'Saft\Rdf\StatementIterator'
        );
    }

    public function testCreateStatementIteratorFromArrayInvalidParameterGiven()
    {
        $this->setExpectedException('\Exception');

        $parameter = array('invalid parameter');
        $this->newInstance()->createStatementIteratorFromArray($parameter);
    }
}
