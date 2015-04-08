<?php
namespace Saft\Backend\LocalStore\Store;

use Saft\Rdf\Statement;

final class MatchingStatementIterator extends \Saft\Rdf\AbstractStatementIterator
{
    private $lineIterator;
    private $pattern = null;
    private $currentStatement;

    public function __construct($filename, Statement $pattern = null)
    {
        if (is_null($filename)) {
            throw new \InvalidArgumentException('$filename is null');
        }
        $this->lineIterator = new IgnoringLineIterator($filename);
        $this->currentStatement = null;
        $this->pattern = $pattern;
    }

    /**
     * Sets the (positive) pattern. rewind should called after.
     * @param Statement $pattern positive pattern
     */
    public function setPattern(Statement $pattern)
    {
        $this->pattern = $pattern;
    }

    /**
     * {@inheritdoc}
     * @return Statement Current matching statement
     */
    public function current()
    {
        $this->ensureNotClosed();
        if (is_null($this->currentStatement)) {
            throw new \Exception('No such element');
        }
        return $this->currentStatement;
    }

    /**
     * {@inheritdoc}
     * @return integer Line number of the current matching statement
     */
    public function key()
    {
        $this->ensureNotClosed();
        return $this->lineIterator->key();
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $this->ensureNotClosed();
        $this->lineIterator->next();
        $this->skipUntilMatch();
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->lineIterator->rewind();
        $this->skipUntilMatch();
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return !is_null($this->currentStatement);
    }

    private function skipUntilMatch()
    {
        $this->ensurePatternIsSet();
        $this->currentStatement = null;
        while ($this->lineIterator->valid()) {
            $line = $this->lineIterator->current();
            try {
                $statement = NtriplesParser::parseStatement($line);
            } catch (SyntaxException $e) {
                // correct the row information
                throw new SyntaxException(
                    $e->getMessage(),
                    $this->lineIterator->key(),
                    $e->getColumn()
                );
            }
            if ($statement->matches($this->pattern)) {
                $this->currentStatement = $statement;
                break;
            }
            $this->lineIterator->next();
        }
    }

    public function close()
    {
        $this->lineIterator->close();
    }

    public function isClosed()
    {
        return $this->lineIterator->isClosed();
    }

    private function ensureNotClosed()
    {
        if ($this->isClosed()) {
            throw new \LogicException('Closed');
        }
    }

    private function ensurePatternIsSet()
    {
        if (is_null($this->pattern)) {
            throw new \LogicException('Pattern not set');
        }
    }
}
