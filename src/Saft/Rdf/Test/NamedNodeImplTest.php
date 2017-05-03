<?php

namespace Saft\Rdf\Test;

use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\RdfHelpers;

class NamedNodeImplTest extends NamedNodeAbstractTest
{
    /**
     * Return a new instance of NamedNodeImpl
     */
    public function newInstance($uri)
    {
        return new NamedNodeImpl(new RdfHelpers(), $uri);
    }
}
