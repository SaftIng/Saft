<?php
namespace Saft\Backend\LocalStore\Store;

/**
 * Iterates over a file line by line, ignoring comment lines.
 * A comment line has a hash '#' as its first character.
 * Because the text file is opened, after using the iterator,
 * you have to close it.
 */
final class CommentIgnoringLineIterator implements \Iterator
{
    private $pointer;
    private $closed = false;
    // null or a non-comment line string
    private $currentLine = null;
    // starting by 0
    private $lineCount = 0;

    /**
     * @param string $filename
     * @throws \Exception When file can't opened
     */
    public function __construct($filename)
    {
        if (is_null($filename)) {
            throw new \InvalidArgumentException('');
        }

        $this->pointer = fopen($filename, 'r');
        if ($this->pointer === false) {
            throw new \Exception('Unable to open ' . $filename);
        }
    }

    /**
     * You must call rewind at first.
     * @return string Current non-comment line
     */
    public function current()
    {
        $this->ensureNotClosed();
        if (!$this->valid()) {
            throw new \Exception('No such element');
        }
        return $this->currentLine;
    }

    /**
     * @return int Line number of the current line starting at 0
     */
    public function key()
    {
        $this->ensureNotClosed();
        if (!$this->valid()) {
            throw new \Exception('No such element');
        }
        return $this->lineCount;
    }

    /**
     * Moves to the next non-comment line.
     */
    public function next()
    {
        $this->ensureNotClosed();
        if (!$this->valid()) {
            throw new \Exception('No such element');
        }
        $this->currentLine = null;
        $this->skipComments();
    }

    /**
     * Reset to the first line.
     */
    public function rewind()
    {
        $this->ensureNotClosed();
        if (rewind($this->pointer) === false) {
            throw new \Exception('Unable to rewind');
        }
        $this->currentLine = null;
        $this->lineCount = 0;
        $this->skipComments();
    }

    /**
     * @return boolean true, if next can called, else false.
     */
    public function valid()
    {
        return !is_null($this->currentLine);
    }
    
    private function skipComments()
    {
        while (!feof($this->pointer)) {
            $line = $this->readLine();
            if (is_null($line)) {
                // feof is not true at start but fgets returns false
                // when dealing with empty files.
                break;
            } elseif (!$this->canIgnore($line)) {
                $this->currentLine = rtrim($line, "\n\r");
                break;
            }
        }
    }

    private function readLine()
    {
        $line = fgets($this->pointer);
        if ($line !== false) {
            $this->lineCount++;
            return $line;
        } else {
            return null;
        }
    }

    const IGNORE_REGEX = '/^\s*(#.*)?$/';

    private function canIgnore($line)
    {
        return preg_match(self::IGNORE_REGEX, $line) === 1;
    }

    /**
     * Close the iterator. The file gets closed. If allready closed,
     * this has no effect.
     */
    public function close()
    {
        if (!is_null($this->pointer)) {
            fclose($this->pointer);
            $this->pointer = null;
            $this->closed = true;
        }
    }

    public function isClosed()
    {
        return $this->closed;
    }

    private function ensureNotClosed()
    {
        if ($this->closed) {
            throw new \Exception('Closed');
        }
    }
}
