<?php

namespace Saft\Rdf;

class BlankNodeImpl extends AbstractBlankNode
{
    protected $blankId;

    /*
     * @param mixed $value
     * @param string $lang optional
     */
    public function __construct($blankId)
    {
        $this->blankId = $blankId;
    }

    public function getBlankId()
    {
        return $this->blankId;
    }
}
