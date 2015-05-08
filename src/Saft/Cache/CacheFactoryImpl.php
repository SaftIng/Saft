<?php

namespace Saft\Cache;

class CacheFactoryImpl implements CacheFactory
{
    /**
     * @param  array $config
     * @throws \Exception If no class information given or PHP class does not exists.
     * @throws \Exception If one of the requirements are not fullfilled.
     */
    public function createCache(array $config)
    {
        if (isset($config['class']) && class_exists($config['class'])) {
            $class = $config['class'];
            return new $class($config);

        } else {
            throw new \Exception('No class information given or PHP class does not exists.');
        }
    }
}
