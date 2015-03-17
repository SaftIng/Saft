<?php
namespace Saft\Rdf;

class StatementImpl implements Statement
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
     * @param NamedNode                    $predicate
     * @param Node                         $object
     * @return
     * @throw
     */
    public function __construct(Node $subject, Node $predicate, Node $object, NamedNode $graph = null)
    {
        $this->subject = $subject;
        $this->predicate = $predicate;
        $this->object = $object;
        $this->graph = $graph;
    }

    /**
     * Builds a string of triples in N-Triples syntax out of a triple array.
     *
     * @param array $triples An array of triples
     * @return string
     */
    public static function buildTripleString(array $triples)
    {
        $sparql = '';

        foreach ($triples as $triple) {
            // TODO add support for blank nodes
            $resource = '<' . trim($triple[0]) . '>';
            $property = '<' . trim($triple[1]) . '>';
            if ('uri' == $triple[2]['type']) {
                $value = '<' . $triple[2]['value'] . '>';

            } else { // == "literal"
                $value = \Saft\Rdf\Literal::buildLiteralString(
                    $triple[2]["value"],
                    true === isset($triple[2]["datatype"]) ? $triple[2]["datatype"] : null,
                    true === isset($triple[2]["lang"]) ? $triple[2]["lang"] : null
                );
            }
            // add triple to the string
            $sparql .= $resource ." ". $property ." ". $value . "." . PHP_EOL;
        }
        return $sparql;
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
    public function isConcrete()
    {
        return $this->subject->isConcrete()
               && $this->predicate->isConcrete()
               && $this->object->isConcrete();
    }

    /**
     * @return boolean
     */
    public function isPattern()
    {
        return $this->subject->isConcrete()
               && $this->predicate->isConcrete()
               && $this->object->isConcrete();
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
     * @return boolean
     */
    public function toNT()
    {
        return $this->getGraph()->toNT() .' '.
               $this->getSubject()->toNT() .' '.
               $this->getPredicate()->toNT() .' '.
               $this->getObject()->toNT() .'.';
    }

    /**
     * @return boolean
     */
    public function toSparqlFormat()
    {
        return $this->getSubject()->toNT() .' '.
               $this->getPredicate()->toNT() .' '.
               $this->getObject()->toNT() .'.';
    }
}
