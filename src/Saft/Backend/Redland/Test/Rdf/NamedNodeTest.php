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
        if (false === function_exists('librdf_new_world')) {
            $this->markTestSkipped('Can not find librdf_new_world function, so it seems Redland is not installed.');
        }

        parent::setUp();
    }
}
