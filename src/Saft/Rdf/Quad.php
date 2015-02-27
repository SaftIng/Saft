<?php

namespace Saft\Rdf;

use \Saft\Rdf\Node;
use \Saft\Rdf\NamedNode;

class Quad extends \Saft\Rdf\AbstractStatement
{
    /**
     * @var string
     */
    protected $graphUri;
    
    /**
     * @param Node $subject
     * @param NamedNode $predicate
     * @param Node $object
     * @param string $graphUri
     */
    public function __construct(Node $subject, NamedNode $predicate, Node $object, $graphUri)
    {
        $this->subject = $subject;
        $this->predicate = $predicate;
        $this->object = $object;

        $this->grahpUri = $graphUri;
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
}
