<?php

namespace Saft\Data;

use Saft\Rdf\StatementIterator;

interface Parser
{
    /**
     * Parses a given target and builds a StatementIterator. The target can be a string, a filename of a file
     * an URL, ... .
     *
     * @param  string            $target
     * @return StatementIterator
     */
    public function parse($target);
}
