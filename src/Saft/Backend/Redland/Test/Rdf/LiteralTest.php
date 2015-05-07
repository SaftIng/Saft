<?php

namespace Saft\Backend\Redland\Tests\Rdf;

use \Saft\Backend\Redland\Rdf\NodeFactory;

class LiteralTest extends \Saft\Rdf\Test\LiteralAbstractTest
{
    public function newInstance($value, $datatype = null, $lang = null)
    {
        $factory = new NodeFactory();
        return $factory->createLiteral($value, $datatype, $lang);
    }

    /**
     * Check for reland extension to be installed before execute a test.
     */
    public function setUp()
    {
        if (false === extension_loaded('redland')) {
            $this->markTestSkipped('Can not find librdf_new_world function, so it seems Redland is not installed.');
        }

        parent::setUp();
    }
}
