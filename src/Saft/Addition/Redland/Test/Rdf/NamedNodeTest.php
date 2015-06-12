<?php

namespace Saft\Addition\Redland\Tests\Rdf;

use Saft\Addition\Redland\Rdf\NodeFactory;

class NamedNodeTest extends \Saft\Rdf\Test\NamedNodeAbstractTest
{
    public function newInstance($uri)
    {
        $nodeFactory = new NodeFactory();
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
