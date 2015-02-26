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
     * Builds a string of triples in N-Triples syntax out of a triple array.
     *
     * @param array $triples An array of triples
     * @return string
     */
    public static function buildTripleString(array $triples)
    {
        $sparql = "";
        
        foreach ($triples as $triple) {
            // TODO: blank nodes
            $resource = "<" . trim($triple[0]) . ">";
            $property = "<" . trim($triple[1]) . ">";
            if ("uri" == $triple[2]["type"]) {
                $value = "<" . $triple[2]["value"] . ">";
            
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
