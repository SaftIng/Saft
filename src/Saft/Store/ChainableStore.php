<?php

namespace Saft\Store;

interface ChainableStore
{
    /**
     * Set successor instance. This method is useful, if you wanna build chain of instances which implement
     * StoreInterface. It sets another instance which will be later called, if a statement- or query-related
     * function gets called.
     * E.g. you chain a query cache and a virtuoso instance. In this example all queries will be handled by
     * the query cache first, but if no cache entry was found, the virtuoso instance gets called.
     */
    public function setChainSuccessor(Store $successor);
}
