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
     * Tests createArrayStatementIterator
     */

    public function testCreateArrayStatementIteratorArrayGiven()
    {
        $this->fixture = $this->newInstance();
        $parameter = array();

        $this->assertClassOfInstanceImplements(
            $this->fixture->createArrayStatementIterator($parameter),
            'Saft\Rdf\StatementIterator'
        );
    }

    public function testCreateArrayStatementIteratorIteratorGiven()
    {
        $this->fixture = $this->newInstance();
        $parameter = new \ArrayIterator(array());

        // get a list of all interfaces that instance implements
        $implements = class_implements();

        $this->assertClassOfInstanceImplements(
            $this->fixture->createArrayStatementIterator($parameter),
            'Saft\Rdf\StatementIterator'
        );
    }

    public function testCreateArrayStatementIteratorInvalidParameterGiven()
    {
        $this->setExpectedException('\Exception');

        $parameter = 'invalid parameter';
        $this->newInstance()->createArrayStatementIterator($parameter);
    }
}
