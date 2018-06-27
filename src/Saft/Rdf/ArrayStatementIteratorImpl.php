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

namespace Saft\Rdf;

class ArrayStatementIteratorImpl implements StatementIterator
{
    /**
     * @var \ArrayIterator over the statements array
     */
    protected $arrayIterator;

    /**
     * @param array $statements array of instances of Statement
     *
     * @throws \Exception if $statements does contain at least one non-Statement instance
     */
    public function __construct(array $statements)
    {
        $checkedStatements = [];

        // check that each entry of the array is of type Statement
        foreach ($statements as $statement) {
            if (false === $statement instanceof Statement) {
                throw new \Exception('Parameter $statements must contain Statement instances.');
            }

            // check for statement doublings
            if ($statement->isConcrete()) {
                $hash = $statement->toNQuads();
            } else {
                $hash = (string) $statement->getSubject()
                    .(string) $statement->getPredicate()
                    .(string) $statement->getObject();
            }
            if (isset($checkedStatements[$hash])) {
                // we already have that statement, go to the next one
            } else {
                $checkedStatements[$hash] = $statement;
            }
        }

        $this->arrayIterator = new \ArrayIterator(\array_values($checkedStatements));
    }

    /**
     * @return Statement
     */
    public function current(): Statement
    {
        return $this->arrayIterator->current();
    }

    /**
     * @return int position in the statements array
     */
    public function key()
    {
        return $this->arrayIterator->key();
    }

    /**
     * Any returned value is ignored.
     */
    public function next()
    {
        $this->arrayIterator->next();
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        return $this->arrayIterator->valid();
    }

    public function rewind()
    {
        $this->arrayIterator->rewind();
    }

    public function toArray(): array
    {
        $stmts = [];

        foreach ($this->arrayIterator as $stmt) {
            $stmts[] = $stmt->toArray();
        }

        return $stmts;
    }
}
