<?php

namespace Saft\Rdf\Test;

use Saft\Rdf\VariableImpl;

class VariableUnitTest extends VariableAbstractTest
{

    /**
     * Return a new instance of VariableImpl
     */
    public function newInstance($name)
    {
        return new VariableImpl($name);
    }
}
