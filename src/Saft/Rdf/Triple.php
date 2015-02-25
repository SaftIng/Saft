<?php

namespace Saft\Rdf;

class Triple extends \Saft\Rdf\AbstractStatement
{
    /**
     * @param \Saft\Rdf\Node $subject
     * @param \Saft\Rdf\NamedNode $predicate
     * @param \Saft\Rdf\Node $object
     */
    public function __construct(\Saft\Rdf\Node $subject, \Saft\Rdf\Node $predicate, 
        \Saft\Rdf\Node $object)
    {
        $this->subject = $subject;
        $this->predicate = $predicate;
        $this->object = $object;
    }
    
    /**
     * @return boolean
     */
    public function isQuad()
    {
        return false;
    }
    
    /**
     * @return boolean
     */
    public function isTriple()
    {
        return true;
    }
}
