<?php

namespace Saft\Store;

interface StoreFactory
{
    /**
     * Creates an instance of Store using given configuration.
     *
     * @param  array $configuration
     * @return Store Instance of Store
     * @throws StoreException If no class to instantiate was given.
     * @throws StoreException If given class was not found.
     */
    public function createInstance(array $config);
}
