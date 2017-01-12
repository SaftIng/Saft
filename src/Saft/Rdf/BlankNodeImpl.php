<?php

namespace Saft\Rdf;

class BlankNodeImpl extends AbstractBlankNode
{
    protected $blankId;

    /*
     * @param string $blankId
     */
    public function __construct($blankId)
    {
        if (is_string($blankId)) {
            $this->blankId = $blankId;
        } else {
            throw new \Exception('Blank nodes have to have a string as $blankId.');
        }
    }

    public function getBlankId()
    {
        return $this->blankId;
    }
}
