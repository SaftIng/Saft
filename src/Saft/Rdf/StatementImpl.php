<?php

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
     * Constructor
     *
     * @param Node $subject
     * @param Node $predicate
     * @param Node $object
     * @param Node $graph
     */
    public function __construct(Node $subject, Node $predicate, Node $object, Node $graph = null)
    {
        $this->subject = $subject;
        $this->predicate = $predicate;
        $this->object = $object;

        if (null !== $graph) {
            $this->graph = $graph;
        }
    }

    /**
     * @return NamedNode
     */
    public function getGraph()
    {
        return $this->graph;
    }

    /**
     * @return NamedNode|BlankNode
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @return NamedNode
     */
    public function getPredicate()
    {
        return $this->predicate;
    }

    /**
     * @return Node
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @return boolean
     */
    public function isQuad()
    {
        return null !== $this->graph;
    }

    /**
     * @return boolean
     */
    public function isTriple()
    {
        return null === $this->graph;
    }
}
