<?php

namespace Saft\Rdf;

abstract class AbstractStatement implements \Saft\Rdf\Statement
{
    /**
     * @var \Saft\Rdf\Node
     */
    protected $object;
    
    /**
     * @var \Saft\Rdf\NamedNode
     */
    protected $predicate;
    
    /**
     * @var \Saft\Rdf\Node
     */
    protected $subject;
}
