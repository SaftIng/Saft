<?php

namespace Saft\Rdf\Test;

use Saft\Rdf\NamedNodeImpl;

class NamedNodeImplUnitTest extends \PHPUnit_Framework_TestCase
{

    /**
     *
     */
    public function newInstance($uri)
    {
        return new NamedNodeImpl($uri);
    }
}
