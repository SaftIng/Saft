<?php

namespace Saft\Backend\EasyRdf\Data;

use EasyRdf_Format;
use EasyRdf_Graph;
use Saft\Backend\EasyRdf\Data\AbstractParser;
use Saft\Data\Parser;
use Saft\Rdf\ArrayStatementIteratorImpl;

class StringParser extends AbstractParser
{
    /**
     * Parses a given string and builds a StatementIterator.
     *
     * @param  string            $target
     * @return StatementIterator
     */
    public function parse($target)
    {
        $graph = new EasyRdf_Graph();
        
        $format = EasyRdf_Format::guessFormat($target);
        $graph->parse($target, $format->getName());
        
        // transform parsed data to PHP array
        return $this->rdfPhpToStatementIterator($graph->toRdfPhp());
    }
}
