<?php
namespace Saft\Cache;

interface CacheInterface
{
    /**
     * Checks that all requirements for this adapter are fullfilled.
     *
     * @return boolean Returns true if all requirements are fullfilled, false otherwise
     * @throws \Exception If one requirement is not fullfilled.
     */
    public function checkRequirements();
    
    /**
     * Removes all entries of the cache instance.
     */
    public function clean();
    
    /**
     * Deletes a certain cache entry by key.
     *
     * @param string $key Key of the cache entry to delete.
     */
    public function delete($key);
    
    /**
     * Returns the value to a given key, if it exists in the cache.
     *
     * @param string $key ID of the entry to return the value from.
     * @return mixed Value of the entry.
     */
    public function get($key);
    
    /**
     * Returns the complete cache entry, which contains additional meta data besides stored value.
     *
     * @param string $key ID of the entry to return the value from.
     * @return array Complete cache entry
     */
    public function getCompleteEntry($key);
    
    /**
     * Returns the type of the cache adapter.
     *
     * @return string Type of the cache adapter.
     */
    public function getType();
    
    /**
     * Checks if an entry is cached.
     *
     * @param string $key ID of the entry to check.
     * @return boolean True, if entry behind given $key exists, false otherwise.
     */
    public function isCached($key);
    
    /**
     * Stores a new entry in the cache or overrides an existing one.
     *
     * @param string $key Identifier of the value to store.
     * @param mixed $value Value to store in the cache.
     */
    public function set($key, $value);
    
    /**
     * Setup cache adapter. All operations to establish a connection to the cache have to be done. It should
     * call checkRequirements to be sure all requirements are fullfilled, before init anything.
     *
     * @param array $config Array containing necessary parameter to setup a cache adapter.
     * @throws \Exception If one requirement is not fullfilled.
     */
    public function setup(array $config);
}
