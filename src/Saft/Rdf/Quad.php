<?php

namespace Saft\Rdf;

class Quad implements Statement
{
    /**
     * @var NamedNode|BlankNode
     */
    protected $subject;

    /**
     * @var NamedNode
     */
    protected $predicate;

    /**
     * @var Node
     */
    protected $object;

    /**
     * @var NamedNode
     */
    protected $graph;
    
    /**
     * @param NamedNode|BlankNode $subject
     * @param NamedNode $predicate
     * @param Node $object
     * @param NamedNode $graph
     */
    public function __construct($subject, NamedNode $predicate, Node $object, NamedNode $graph)
    {
        $this->subject = $subject;
        $this->predicate = $predicate;
        $this->object = $object;

        $this->grahp = $graph;
    }
    
    /**
     * @return boolean
     */
    public function isQuad()
    {
        return true;
    }
    
    /**
     * @return boolean
     */
    public function isTriple()
    {
        return false;
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
     * @return NamedNode
     */
    public function getGraph()
    {
        return $this->graph;
    }
}
