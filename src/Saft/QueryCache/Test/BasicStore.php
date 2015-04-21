<?php

namespace Saft\QueryCache\Test;

use Saft\Store\AbstractTriplePatternStore;
use Saft\Store\Store;

/**
 * Basic class. Its purpose is to serve in test cases as a successor for a QueryCache instance. This implementation
 * assumes that all triple and query related methods of AbstractTriplePatternStore only return their parameter.
 */
class BasicStore extends AbstractTriplePatternStore implements Store
{
    /**
     * Has no function and returns an empty array.
     *
     * @return array Empty array
     */
    public function getAvailableGraphs()
    {
        return array();
    }
    
    /**
     * Has no function and returns an empty array.
     *
     * @return array Empty array
     */
    public function getStoreDescription()
    {
        return array();
    }
}
