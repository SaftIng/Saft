<?php
namespace Saft\Backend\Redland\Rdf;

class RedlandLiteralFactory
{
    public function newInstance($value, $lang = null, $datatype = null)
    {
        return new Literal($value, $lang, $datatype);
    }
}
