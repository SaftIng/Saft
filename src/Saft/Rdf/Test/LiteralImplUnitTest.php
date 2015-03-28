<?php
namespace Saft\Rdf\Test;

use Saft\Rdf\LiteralImpl;

class LiteralImplUnitTest extends LiteralAbstractTest
{

    /**
     * Return a new instance of LiteralImpl
     */
    public function newInstance($value, $lang = null, $datatype = null)
    {
        return new LiteralImpl($value, $lang);
    }
}
