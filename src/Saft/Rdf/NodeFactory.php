<?php

namespace Saft\Rdf;

class NodeFactory
{
    /**
     *
     * @param
     * @return
     * @throws
     */
    public function __construct()
    {
    }
    
    /**
     * @param string $value
     * @param string $type
     * @return Node
     * @throws \Exception If unknown type was given
     */
    public function getInstance($value, $type)
    {
        /**
         * URI
         */
        if ('uri' == $type) {
            return new NamedNodeImpl($value);
        
        /**
         * Variable
         */
        } elseif ('var' == $type) {
            return new VariableImpl('?' . str_replace('?', '', $value));
        
        /**
         * Typed Literal or Literal
         */
        } elseif ('typed-literal' == $type || 'literal' == $type) {
            return new LiteralImpl($value);
        
        /**
         * Unknown type
         */
        } else {
            throw new \Exception('Unknown type given: '. $type);
        }
    }
}
