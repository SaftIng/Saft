<?php

namespace Saft\Store;

/**
 * Predefined Pattern-statement Store. The Triple-methods need to be implemented in the specific statement-store.
 * The query method is defined in the abstract class and reroute to the triple-methods.
 */
abstract class AbstractTriplePatternStore implements StoreInterface
{
    public function query($query, array $options = array())
    {
        //@TODO
        if (stristr($a, 'select') || stristr($a, 'construct')) {
            $this->get($a);
        } else if (stristr($a, 'deslete')) {
            $this->delete($a);
        } else if (stristr($a, 'insert')) {
            $this->delete($a);
        }
    }
}
