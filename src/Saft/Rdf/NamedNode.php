<?php

namespace Saft\Rdf;

interface NamedNode extends Node
{
    /**
     * @return string URI of the node.
     */
    public function getUri();
}
