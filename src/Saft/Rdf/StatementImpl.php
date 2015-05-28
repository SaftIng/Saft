<?php

namespace Saft\Rdf;

class StatementImpl extends AbstractStatement
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
     *
     * @param NamedNode|BlankNode|Variable $subject
     * @param NamedNode|Variable           $predicate
     * @param Node                         $object
     * @param NodeNode|Variable            $graph
     * @return
     * @throw
     */
    public function __construct(Node $subject, Node $predicate, Node $object, Node $graph = null)
    {
        $this->setSubject($subject);
        $this->setPredicate($predicate);
        $this->setObject($object);

        if (null !== $graph) {
            $this->setGraph($graph);
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

    /**
     * @param NamedNode|Variable $graph
     */
    public function setGraph(Node $graph)
    {
        $this->graph = $graph;
    }

    /**
     * @param Node $object
     */
    public function setObject(Node $object)
    {
        $this->object = $object;
    }

    /**
     * @param NamedNode|Variable $predicate
     */
    public function setPredicate(Node $predicate)
    {
        $this->predicate = $predicate;
    }

    /**
     * @param BlankNode|NamedNode $subject
     */
    public function setSubject(Node $subject)
    {
        $this->subject = $subject;
    }
}
