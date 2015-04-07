<?php

namespace Saft\Rdf;

interface Node
{
    /**
     * @return string
     */
    public function __toString();

    /**
     * Check if a given instance of Node is equal to this instance.
     *
     * @param  Node    $toCompare Node instance to check against.
     * @return boolean            True, if both instances are semantically equal, false otherwise.
     */
    public function equals(\Saft\Rdf\Node $toCompare);
    
    /**
     * @return boolean
     */
    public function isConcrete();

    /**
     * @return boolean
     */
    public function isLiteral();

    /**
     * @return boolean
     */
    public function isNamed();

    /**
     * @return boolean
     */
    public function isBlank();

    /**
     * @return boolean
     */
    public function isVariable();

    /**
     * @return string
     */
    public function toNQuads();

    /**
     * Returns true, if this matches the given pattern. This have to be concrete. The given pattern can either
     * a variable node or a concrete node. Only concrete nodes of the same type can match.
     *
     * @param  Node    $pattern Node instance to check against.
     * @return boolean          True, if this matches the pattern, otherwise false.
     * @throws \LogicException  If isConcrete() returns true.
     */
    public function matches(Node $pattern);
}
