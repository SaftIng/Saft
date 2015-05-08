<?php

namespace Saft\Cache;

interface CacheFactory
{
    /**
     * Creates a usable cache instance.
     *
     * @param array $config Configuration array containing all information the backend need to initialize the instance.
     */
    public function createCache(array $config);
}
