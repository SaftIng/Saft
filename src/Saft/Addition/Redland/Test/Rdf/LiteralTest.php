<?php

namespace Saft\Addition\Redland\Tests\Rdf;

use Saft\Addition\Redland\Rdf\NodeFactory;
use Saft\Rdf\Node;

/**
 *
 * @requires extension redland
 */
class LiteralTest extends \Saft\Rdf\Test\LiteralAbstractTest
{
    /**
     * Return a new instance of redland Literal
     */
    public function newInstance($value, Node $datatype = null, $lang = null)
    {
        $factory = new NodeFactory();
        return $factory->createLiteral($value, $datatype, $lang);
    }

    public function getNodeFactory()
    {
        return new NodeFactory();
    }
}
