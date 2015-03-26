<?php
namespace Saft\Rdf\Test;

class LiteralImplTestFactory
{
    public function newInstance($value, $param = null)
    {
        return new LiteralImpl($value, $param);
    }
}
