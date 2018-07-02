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

class StatementImpl extends AbstractStatement
{
    /**
     * @var Node
     */
    protected $subject;

    /**
     * @var Node
     */
    protected $predicate;

    /**
     * @var Node
     */
    protected $object;

    /**
     * @var Node
     */
    protected $graph;

    /**
     * Constructor.
     *
     * @param Node $subject
     * @param Node $predicate
     * @param Node $object
     * @param Node $graph
     */
    public function __construct(Node $subject, Node $predicate, Node $object, Node $graph = \null)
    {
        $this->subject = $subject;
        $this->predicate = $predicate;
        $this->object = $object;

        if (\null !== $graph) {
            $this->graph = $graph;
        }
    }

    /**
     * @return Node|null
     */
    public function getGraph()
    {
        return $this->graph;
    }

    /**
     * @return NamedNode|BlankNode
     */
    public function getSubject(): Node
    {
        return $this->subject;
    }

    /**
     * @return Node
     */
    public function getPredicate(): Node
    {
        return $this->predicate;
    }

    /**
     * @return Node
     */
    public function getObject(): Node
    {
        return $this->object;
    }

    /**
     * @return bool
     */
    public function isQuad(): bool
    {
        return null !== $this->graph;
    }

    /**
     * @return bool
     */
    public function isTriple(): bool
    {
        return null === $this->graph;
    }

    /**
     * Transforms the Statement object into an array with a structure like:.
     *
     *      array(
     *          's' => 'http://foo'
     *      )
     *
     * @return array array representation of the Statement
     */
    public function toArray(): array
    {
        if ($this->isConcrete()) {
            $stmt = [
                's' => (string) $this->getSubject(),
                'p' => (string) $this->getPredicate(),
                'o' => (string) $this->getObject(),
            ];

            // if graph is available, but is not a pattern
            if ($this->getGraph() instanceof Node && false === $this->getGraph()->isPattern()) {
                $stmt['g'] = (string) $this->getGraph();
            }

            return $stmt;
        } else {
            throw new \Exception('Only concrete statements are supported. Yours contains at least one AnyPattern instance.');
        }
    }
}
