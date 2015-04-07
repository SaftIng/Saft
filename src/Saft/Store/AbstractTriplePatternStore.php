<?php

namespace Saft\Store;

/**
 * Predefined Pattern-statement Store. The Triple-methods need to be implemented in the specific statement-store.
 * The query method is defined in the abstract class and reroute to the triple-methods.
 */
abstract class AbstractTriplePatternStore implements StoreInterface
{
    /**
     * @param string $query SPARQL query string.
     * @return ?
     * @throws ?
     */
    public function delete($query)
    {
        return $query;
    }
    
    /**
     * @param string $query SPARQL query string.
     * @return ?
     * @throws ?
     */
    public function get($query)
    {
        return $query;
    }
    
    /**
     * @param string $query            SPARQL query string.
     * @param string $options optional Further configurations.
     * @throws ?
     */
    public function query($query, array $options = array())
    {
        //@TODO
        if (stristr($query, 'select') || stristr($query, 'construct')) {
            $this->get($query);
        } elseif (stristr($query, 'delete')) {
            $this->delete($query);
        } elseif (stristr($query, 'insert')) {
            $this->delete($query);
        }
    }
}
