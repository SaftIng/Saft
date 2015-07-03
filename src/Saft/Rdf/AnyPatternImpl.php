<?php

namespace Saft\Rdf;

/**
 * This interface represents a pattern, in some contexts also a variable. Its purpose is to act as some kind of
 * a placeholder, if you dont want to specify a RDF term.
 *
 * It is useful in SPARQL queries, to be used as a variable: SELECT ?s WHERE { ?s ?p ?o }
 */
class AnyPatternImpl implements Node
{
    /**
     * Checks if this instance is a blank node.
     *
     * @return boolean True, if this instance is a blank node, false otherwise.
     */
    public function isBlank()
    {
        return false;
    }

    /**
     * Checks if this instance is concrete, which means it does not contain pattern.
     *
     * @return boolean True, if this instance is concrete, false otherwise.
     */
    public function isConcrete()
    {
        return false;
    }

    /**
     * Checks if this instance is a literal.
     *
     * @return boolean True, if it is a literal, false otherwise.
     */
    public function isLiteral()
    {
        return false;
    }

    /**
     * Checks if this instance is a named node.
     *
     * @return boolean True, if it is a named node, false otherwise.
     */
    public function isNamed()
    {
        return false;
    }

    /**
     * Checks if this instance is a pattern. It can either be a pattern or concrete.
     *
     * @return boolean True, if this instance is a pattern, false otherwise.
     */
    public function isPattern()
    {
        return true;
    }

    /**
     * Check if a given instance of Node is equal to this instance.
     *
     * @param Node $toCompare Node instance to check against.
     * @return boolean True, if both instances are semantically equal, false otherwise.
     */
    public function equals(Node $toCompare)
    {
        // Only compare, if given instance is an AnyPattern too
        return $toCompare instanceof AnyPatternImpl;
    }

    /**
     * Returns always true, because a pattern is like a placeholder and can be anything.
     *
     * @param Node $toMatch Node instance to apply the pattern on
     * @return boolean Always true.
     */
    public function matches(Node $toMatch)
    {
        return true;
    }

    /**
     * This method is ment for getting some kind of human readable string
     * representation of the current node.
     *
     * @return string a human readable string representation of the node
     */
    public function __toString()
    {
        return "ANY";
    }

    /**
     * Transform this Node instance to a n-quads string, if possible.
     *
     * @return string N-quads string representation of this instance.
     * @throws \Exception if no n-quads representation is available.
     */
    public function toNQuads()
    {
        throw new \Exception("The AnyPattern is not valid in NQuads");
    }
}
