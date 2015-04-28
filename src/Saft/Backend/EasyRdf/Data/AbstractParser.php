<?php

namespace Saft\Backend\EasyRdf\Data;

use Saft\Data\Parser;
use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\LiteralImpl;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\NodeUtils;
use Saft\Rdf\StatementImpl;

abstract class AbstractParser implements Parser
{
    /**
     * @var StatementIterator
     */
    protected $statementIterator;
    
    /**
     * @param  array             $rdfPhp
     * @return StatementIterator
     */
    protected function rdfPhpToStatementIterator(array $rdfPhp)
    {
        $this->statementIterator = new ArrayStatementIteratorImpl(array());
        
        // go through all subjects
        foreach ($rdfPhp as $subject => $predicates) {
            // predicates associated with the subject
            foreach ($predicates as $property => $objects) {
                // object(s)
                foreach ($objects as $object) {
                    /**
                     * Create subject node
                     */
                    if (true === NodeUtils::simpleCheckURI($subject)) {
                        $s = new NamedNodeImpl($subject);
                    } else {
                        $s = new LiteralImpl($subject);
                    }
                    
                    /**
                     * Create predicate node
                     */
                    if (true === NodeUtils::simpleCheckURI($property)) {
                        $p = new NamedNodeImpl($property);
                    } else {
                        $p = new LiteralImpl($property);
                    }
                    
                    /**
                     * Create object node
                     */
                    if (true === NodeUtils::simpleCheckURI($object['value'])) {
                        $o = new NamedNodeImpl($object['value']);
                    } else {
                        $o = new LiteralImpl($object['value']);
                    }
                    
                    // build statement
                    $newStatement = new StatementImpl($s, $p, $o);
                    
                    $this->statementIterator->append($newStatement);
                }
            }
        }
        
        return $this->statementIterator;
    }
}
