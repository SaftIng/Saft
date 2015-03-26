<?php

namespace Saft\Backend\PhpArrayCache\Cache;

use Saft\Cache\CacheInterface;

class PHPArray implements CacheInterface
{
    /**
     * Checks that all requirements for this adapter are fullfilled.
     *
     * @return boolean Returns true if all requirements are fullfilled, false otherwise
     * @throws \Exception If one requirement is not fullfilled.
     */
    public function checkRequirements()
    {
        // no requirements to fullfill.

        return true;
    }

    /**
     * Removes all entries of the cache instance.
     */
    public function clean()
    {
        $this->cache = array();
    }

    /**
     * Deletes a certain cache entry by key.
     *
     * @param string $key Key of the cache entry to delete.
     */
    public function delete($key)
    {
        unset($this->cache[$key]);
    }

    /**
     * Returns the value to a given key, if it exists in the cache.
     *
     * @param string $key ID of the entry to return the value from.
     * @return mixed Value of the entry.
     */
    public function get($key)
    {
        if (true === $this->isCached($key)) {
            return json_decode($this->cache[$key], true);
        } else {
            return null;
        }
    }

    /**
     * Returns the type of the cache adapter.
     *
     * @return string Type of the cache adapter.
     */
    public function getType()
    {
        return 'phparray';
    }

    /**
     * Checks if an entry is cached.
     *
     * @param string $key ID of the entry to check.
     * @return boolean True, if entry behind given $key exists, false otherwise.
     */
    public function isCached($key)
    {
        return true === isset($this->cache[$key]);
    }

    /**
     * Stores a new entry in the cache or overrides an existing one.
     *
     * @param string $key Identifier of the value to store.
     * @param mixed $value Value to store in the cache.
     */
    public function set($key, $value)
    {
        $this->cache[$key] = json_encode($value);
    }

    /**
     * Setup cache adapter. All operations to establish a connection to the cache have to be done. It should
     * call checkRequirements to be sure all requirements are fullfilled, before init anything.
     *
     * @param array $config Array containing necessary parameter to setup a cache adapter.
     * @throws \Exception If one requirement is not fullfilled.
     */
    public function setup(array $config)
    {
        $this->cache = array();
        $this->config = $config;
    }
}
