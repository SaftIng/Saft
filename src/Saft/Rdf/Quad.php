<?php

namespace Saft\Rdf;

class Quad extends \Saft\Rdf\AbstractStatement
{
    /**
     * @var string
     */
    protected $graphUri;
    
    /**
     * @param \Saft\Rdf\Node $subject
     * @param \Saft\Rdf\NamedNode $predicate
     * @param \Saft\Rdf\Node $object
     * @param string $graphUri
     */
    public function __construct(\Saft\Rdf\Node $subject, \Saft\Rdf\Node $predicate, 
        \Saft\Rdf\Node $object, $graphUri)
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
