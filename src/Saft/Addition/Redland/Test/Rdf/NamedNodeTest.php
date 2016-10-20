<?php

namespace Saft\Addition\Redland\Tests\Rdf;

use Saft\Addition\Redland\Rdf\NodeFactory;
use Saft\Rdf\NodeUtils;
use Saft\Rdf\Test\NamedNodeAbstractTest;

class NamedNodeTest extends NamedNodeAbstractTest
{
    public function newInstance($uri)
    {
        $nodeFactory = new NodeFactory(new NodeUtils());
        return $nodeFactory->createNamedNode($uri);
    }

    /**
     * Check for reland extension to be installed before execute a test.
     */
    public function setUp()
    {
        if (false === extension_loaded('redland')) {
            $this->markTestSkipped('Extension redland is not loaded.');
        }

        parent::setUp();
    }
}
