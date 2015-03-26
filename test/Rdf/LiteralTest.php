<?php
namespace Saft\Backend\Redland\Tests\Rdf;

class LiteralTest extends \Saft\Rdf\Test\LiteralAbstractTest
{
    public function __construct()
    {
        $this->factory = new \Saft\Backend\Redland\Rdf\RedlandLiteralFactory();
    }
    public function newInstance($value, $lang = null, $datatype = null)
    {
        return $this->factory->newInstance($value, $lang, $datatype);
    }
}
