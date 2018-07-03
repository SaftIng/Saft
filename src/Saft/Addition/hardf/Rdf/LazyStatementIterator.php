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

namespace Saft\Addition\hardf\Rdf;

use pietercolpaert\hardf\TriGParser;
use pietercolpaert\hardf\TriGWriter;
use pietercolpaert\hardf\Util;
use Saft\Rdf\NodeFactory;
use Saft\Rdf\Statement;
use Saft\Rdf\StatementFactory;
use Saft\Rdf\StatementIterator;

/**
 * This StatementIterator instance utilizes chunk based file-parsing of hardf. This allows
 * a fixed memory usage, regardless of the file size.
 *
 * Can only be used for n-triple formated RDF files!
 */
class LazyStatementIterator implements StatementIterator
{
    protected $baseUri;
    protected $currentStatement;
    protected $currentFileLine;
    protected $nodeFactory;
    protected $parser;
    protected $serialization;
    protected $statementFactory;

    /**
     * @param string           $stream
     * @param string           $serialization
     * @param NodeFactory      $nodeFactory
     * @param StatementFactory $statementFactory
     * @param string           $baseUri Default is null
     */
    public function __construct(
        string $stream,
        string $serialization,
        NodeFactory $nodeFactory,
        StatementFactory $statementFactory,
        string $baseUri = null
    ) {
        $this->baseUri = $baseUri;
        $this->nodeFactory = $nodeFactory;
        $this->statementFactory = $statementFactory;
        $this->serialization = $serialization;
        $this->stream = $stream;

        $this->initParser($this->stream, $this->serialization, $this->baseUri);
    }

    public function __destruct()
    {
        // close file handle
        $this->file = null;
    }

    /**
     * @param string $stream
     * @param string $serialization
     * @param string $baseUri Default is null
     */
    protected function initParser(string $stream, string $serialization, string $baseUri = null)
    {
        $this->currentStatement = null;
        $this->currentFileLine = 0;

        $this->parser = new TriGParser(
            [
                'documentIRI' => $baseUri,
                'format' => $serialization
            ]
        );

        $this->file = new \SplFileObject($stream);
    }

    protected function loadNextStatement()
    {
        if (false === $this->file->eof()) {
            $line = $this->file->fgets();

            if (empty($line)) {
                // stop here, if empty string was read, which means we reached the file end.
                return;
            }

            // we expect only 1 triple
            $triple = $this->parser->parseChunk($line);

            // break out, if file line could not be parsed, because we dont want to skip entries.
            if (0 == \count($triple)) {
                throw new \Exception('File line could not be parsed: '. $line);
            }

            $this->currentStatement = saftAdditionHardfTripleToStatement(
                $triple[0],
                $this->nodeFactory,
                $this->statementFactory
            );

            ++$this->currentFileLine;
        }
    }

    /**
     * @return Statement
     */
    public function current(): ?Statement
    {
        if (null == $this->currentStatement) {
            $this->loadNextStatement();
        }

        return $this->currentStatement;
    }

    /**
     * @return int Current file line.
     */
    public function key()
    {
        return $this->currentFileLine;
    }

    /**
     * Any returned value is ignored.
     */
    public function next()
    {
        $this->currentStatement = null;
        $this->loadNextStatement();
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        return false === $this->file->eof();
    }

    public function rewind()
    {
        $this->initParser($this->stream, $this->serialization, $this->baseUri);
    }

    /**
     * Attention: This parses the whole referenced file and returns a simplified representation.
     */
    public function toArray(): array
    {
        $stmts = [];

        foreach ($this as $stmt) {
            $stmts[] = $stmt->toArray();
        }

        return $stmts;
    }
}
