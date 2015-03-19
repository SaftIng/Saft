<?php

namespace Saft\Rdf;

/**
 * Interface Variable
 */
interface Variable extends Node
{
    /**
     * @return string
     */
    public function getName();
}
