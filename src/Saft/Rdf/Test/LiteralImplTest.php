<?php

namespace Saft\Rdf\Test;

use Saft\Rdf\LiteralImpl;
use Saft\Rdf\Node;
use Saft\Rdf\NodeFactoryImpl;

class LiteralImplTest extends LiteralAbstractTest
{
    /**
     * Return a new instance of LiteralImpl
     */
    public function newInstance($value, Node $datatype = null, $lang = null)
    {
        return new LiteralImpl($value, $datatype, $lang);
    }

    public function getNodeFactory()
    {
        return new NodeFactoryImpl();
    }
}
