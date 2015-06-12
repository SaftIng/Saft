<?php

namespace Saft\Addition\Redland\Rdf;

use Saft\Rdf\StatementIterator as SaftStatementIterator;

class StatementIterator implements SaftStatementIterator
{
    /**
     * @var librdf_stream the redland stream wrapped by this class
     */
    protected $stream;

    /**
     * @param $stream librdf_stream
     */
    public function __construct($stream)
    {
        $this->stream = $stream;
    }

    public function current()
    {
        $redlandStatement = librdf_stream_get_object($this->stream);
        return new Statement($redlandStatement);
    }

    public function next()
    {
        librdf_stream_next($this->stream);
    }

    public function key()
    {
        $this->current();
    }

    public function rewind()
    {
        // Does nothing, because this Iterator can't be rewinded
    }

    public function valid()
    {
        if (librdf_stream_end($this->stream)) {
            return false;
        }

        return true;
    }
}
