<?php

namespace Saft\Addition\Redland\Tests\Rdf;

use Saft\Addition\Redland\Rdf\NodeFactory;
use Saft\Rdf\NodeUtils;
use Saft\Rdf\Test\NodeFactoryAbstractTest;

class NodeFactoryTest extends NodeFactoryAbstractTest
{
    /**
     * An abstract method which returns new instances of NodeFactory
     */
    public function getFixture()
    {
        return new NodeFactory(new NodeUtils());
    }

    /**
     *
     */
    public function setUp()
    {
        if (false === extension_loaded('redland')) {
            $this->markTestSkipped('Extension redland is not loaded.');
        }

        parent::setUp();
    }
}
