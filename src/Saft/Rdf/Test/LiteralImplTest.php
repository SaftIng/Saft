<?php

namespace Saft\Rdf\Test;

use Saft\Rdf\LiteralImpl;
use Saft\Rdf\Node;
use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\RdfHelpers;

class LiteralImplTest extends LiteralAbstractTest
{
    /**
     * Return a new instance of LiteralImpl
     */
    public function newInstance($value, Node $datatype = null, $lang = null)
    {
        return new LiteralImpl(new RdfHelpers(), $value, $datatype, $lang);
    }

    public function getNodeFactory()
    {
        return new NodeFactoryImpl(new RdfHelpers());
    }
}
