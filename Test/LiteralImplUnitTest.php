<?php
namespace Saft\Rdf;

class LiteralImplUnitTest extends Test\LiteralAbstractTest
{

    /**
     * Return a new instance of LiteralImpl
     */
    public function newInstance($value, $param = null)
    {
        return new LiteralImpl($value, $param);
    }
}
