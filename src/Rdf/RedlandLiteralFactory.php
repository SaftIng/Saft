<?php
namespace Saft\Backend\Redland\Rdf;

class RedlandLiteralFactory
{
    public function newInstance($value, $datatype = null, $lang = null)
    {
        return new Literal($value, $datatype, $lang);
    }
}
