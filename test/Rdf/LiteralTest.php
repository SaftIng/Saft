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
}
