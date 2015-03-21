<?php
namespace Saft\Rdf;

class LiteralImplTestFactory
{
    public function newInstance($value, $param = null)
    {
        return new LiteralImpl($value, $param);
    }
}
