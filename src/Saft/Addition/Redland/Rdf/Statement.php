<?php

namespace Saft\Addition\Redland\Rdf;

use Saft\Rdf\AbstractStatement;
use Saft\Rdf\Node;

class Statement extends AbstractStatement
{
    /**
     * @var librdf_node the wrapped redland node
     */
    protected $redlandStatement;

    protected $graph;

    public function __construct($redlandStatement, Node $graph = null)
    {
        $this->redlandStatement = $redlandStatement;
        $this->graph = $graph;
    }

    /**
     * Return Statements subject.
     * @return NamedNode|BlankNode
     */
    public function getSubject()
    {
        return $this->getNodeForRedlandNode(librdf_statement_get_subject($this->redlandStatement));
    }

    /**
     * Return Statements predicate
     * @return NamedNode
     */
    public function getPredicate()
    {
        return $this->getNodeForRedlandNode(librdf_statement_get_predicate($this->redlandStatement));
    }

    /**
     * Return Statements object.
     * @return Node
     */
    public function getObject()
    {
        return $this->getNodeForRedlandNode(librdf_statement_get_object($this->redlandStatement));
    }

    /**
     * @return NamedNode|null
     */
    public function getGraph()
    {
        return $this->graph;
    }

    /**
     * @return boolean
     */
    public function isQuad()
    {
        if ($this->graph == null) {
            return false;
        }

        return true;
    }

    /**
     * @return boolean
     */
    public function isTriple()
    {
        return true;
    }

    protected function getNodeForRedlandNode($redlandNode)
    {
        if (librdf_node_is_literal($redlandNode)) {
            return new Literal($redlandNode);
        } elseif (librdf_node_is_resource($redlandNode)) {
            return new NamedNode($redlandNode);
        } elseif (librdf_node_is_blank($redlandNode)) {
            return new BlankNode($redlandNode);
        }
    }
}
