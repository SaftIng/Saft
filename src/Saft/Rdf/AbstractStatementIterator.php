<?php
namespace Saft\Rdf;

abstract class AbstractStatementIterator implements StatementIterator
{
    /**
     * May not be implemented
     */
    public function rewind()
    {
        // Nothing to do
    }
}
