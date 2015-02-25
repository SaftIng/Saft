<?php

namespace Saft\Rdf;

interface Statement
{
    /**
     * @return boolean
     */
    public function isQuad();
    
    /**
     * @return boolean
     */
    public function isTriple();
}
