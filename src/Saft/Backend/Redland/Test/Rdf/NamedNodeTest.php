<?php

namespace Saft\Backend\Redland\Tests\Rdf;

class NamedNodeTest extends \Saft\Rdf\Test\NamedNodeAbstractTest
{
    public function newInstance($uri)
    {
        return new \Saft\Backend\Redland\Rdf\NamedNode($uri);
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
