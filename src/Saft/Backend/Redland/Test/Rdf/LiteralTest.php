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
            $this->markTestSkipped('Extension redland is not loaded.');
        }

        parent::setUp();
    }
}
