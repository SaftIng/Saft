<?php
namespace Saft\Rdf;

class LiteralImplTest extends Test\LiteralAbstractTest
{

    /**
     * Return a new instance of LiteralImpl
     */
    public function newInstance($value, $param = null)
    {
        return new LiteralImpl($value, $param);
    }
}
