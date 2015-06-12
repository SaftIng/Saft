<?php

namespace Saft\Addition\MemcacheD\Cache;

use Saft\Cache\Cache;

class MemcacheD implements Cache
{
    /**
     * Instance of the MemcacheD class.
     *
     * @var \MemcacheD
     */
    protected $cache;

    /**
     * The purpose of this string is to avoid collisions with other non-Saft related cache entries in MemcacheD.
     *
     * @var string
     */
    protected $keyPrefix = 'saft--';

    /**
     * SSetup cache adapter. All operations to establish a connection to the cache have to be done. It should
     * call checkRequirements to be sure all requirements are fullfilled, before init anything.
     *
     * @param  array $config Array containing necessary parameter to setup the cache adapter.
     * @throws \Exception If one requirement is not fullfilled.
     */
    public function __construct(array $config)
    {
        $this->checkRequirements();

        $this->cache = new \Memcached('Saft\\Addition\\Cache\\MemcacheD');
        $servers = $this->cache->getServerList();

        // set default host and port
        $config['host'] = true == isset($config['host']) ? $config['host'] : '127.0.0.1';
        $config['port'] = 0 < (int)$config['port'] ? (int)$config['port'] : 11211;

        // check if the host-port-combination is already in the server list, to avoid adding the same
        // configuration multiple times
        if (true === empty($servers)) {
            $this->cache->setOption(\Memcached::OPT_RECV_TIMEOUT, 1000);
            $this->cache->setOption(\Memcached::OPT_SEND_TIMEOUT, 3000);
            $this->cache->setOption(\Memcached::OPT_TCP_NODELAY, true);
            $this->cache->addServer($config['host'], $config['port']);
        }

        $this->config = $config;

        // save key prefix if it was given
        if (isset($config['keyPrefix'])) {
            $this->keyPrefix = $config['keyPrefix'];
        }

        /*
         * Checks, if we can set and get stuff from MemcacheD. If that does not work, throw an exception and
         * stop working.
         */
        $this->cache->set($this->keyPrefix . 'memcached-test', true);
        if (true !== $this->cache->get($this->keyPrefix . 'memcached-test')) {
            throw new \Exception('MemcacheD requirements fullfilled, but it seems it can\'t set and get values.');
        }
    }

    /**
     * Checks that all requirements for this adapter are fullfilled.
     *
     * @return boolean Returns true if all requirements are fullfilled, false otherwise
     * @throws \Exception If one requirement is not fullfilled.
     */
    public function checkRequirements()
    {
        if (false === extension_loaded('memcached')) {
            throw new \Exception('Memcached extension is not available.');
        } elseif (false === class_exists('\Memcached')) {
            throw new \Exception('Class \Memcached does not exist.');
        } else {
            return true;
        }
    }

    /**
     * Removes all entries of the cache instance.
     */
    public function clean()
    {
        $this->cache->flush();
    }

    /**
     * Deletes a certain cache entry by key.
     *
     * @param string $key Key of the cache entry to delete.
     */
    public function delete($key)
    {
        $hashedKey = $this->keyPrefix . hash('sha256', $key);

        return $this->cache->delete($this->keyPrefix . $hashedKey);
    }

    /**
     * Returns the value of an entry, if it exists in the cache.
     *
     * @param  string $key ID of the entry.
     * @return mixed Value of the entry.
     */
    public function get($key)
    {
        $entry = $this->getCompleteEntry($key);
        return null !== $entry ? $entry['value'] : null;
    }

    /**
     * Returns the complete cache entry to a given key, if it exists in the cache.
     *
     * @param  string $key ID of the entry.
     * @return mixed Value of the entry.
     */
    public function getCompleteEntry($key)
    {
        $hashedKey = $this->keyPrefix . hash('sha256', $key);

        if (true === $this->isCached($key)) {
            $container = $this->cache->get($this->keyPrefix . $hashedKey);

            /**
             * Update meta data
             */
            ++$container['get_count'];
            $this->cache->set($this->keyPrefix . $hashedKey, $container);

            return $container;

        } else {
            return null;
        }
    }

    /**
     * Checks if an entry is cached.
     *
     * @param string $key ID of the entry to check.
     * @return boolean True, if entry behind given $key exists, false otherwise.
     */
    public function isCached($key)
    {
        $hashedKey = $this->keyPrefix . hash('sha256', $key);
        return false !== $this->cache->get($this->keyPrefix . $hashedKey);
    }

    /**
     * Stores a new entry in the cache or overrides an existing one.
     *
     * @param string $key Identifier of the value to store.
     * @param mixed $value Value to store in the cache.
     */
    public function set($key, $value)
    {
        $hashedKey = $this->keyPrefix . hash('sha256', $key);

        if (true === $this->isCached($key)) {
            $container = $this->cache->get($this->keyPrefix . $hashedKey);
            $container['value'] = $value;

            ++$container['set_count'];
        } else {
            $container = array();
            $container['get_count'] = 0;
            $container['set_count'] = 1;
            $container['value'] = $value;
        }

        $this->cache->set($this->keyPrefix . $hashedKey, $container);
    }
}
