<?php

/*
 * This file is part of Saft.
 *
 * (c) Konrad Abicht <hi@inspirito.de>
 * (c) Natanael Arndt <arndt@informatik.uni-leipzig.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Saft\Addition\Redland\Rdf;

use Saft\Rdf\StatementIterator as SaftStatementIterator;

/**
 * @deprecated
 */
class StatementIterator implements SaftStatementIterator
{
    /**
     * @var librdf_stream the redland stream wrapped by this class
     */
    protected $stream;

    /**
     * @param librdf_stream $stream
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
