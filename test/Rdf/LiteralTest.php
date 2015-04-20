<?php
namespace Saft\Backend\Redland\Tests\Rdf;

class LiteralTest extends \Saft\Rdf\Test\LiteralAbstractTest
{
    public function __construct()
    {
        $this->factory = new \Saft\Backend\Redland\Rdf\RedlandLiteralFactory();
    }
    public function newInstance($value, $datatype = null, $lang = null)
    {
        return $this->factory->newInstance($value, $datatype, $lang);
    }
}
