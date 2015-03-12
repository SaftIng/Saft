<?php

namespace Saft\Cache\Adapter;

use Saft\Cache\CacheInterface;

class MemcacheD implements CacheInterface
{
    /**
     * Instance of the MemcacheD class.
     *
     * @var \MemcacheD
     */
    protected $cache;

    /**
     * The purpose of this string is to avoid collisions with other non-Enable
     * related cache entries in MemcacheD.
     *
     * @var string
     */
    protected $keyPrefix;
    
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
        return $this->cache->delete($this->keyPrefix . $key);
    }

    /**
     * Returns the value of an entry, if it exists in the cache.
     *
     * @param  string $key ID of the entry.
     * @return mixed Value of the entry.
     */
    public function get($key)
    {
        $value = $this->cache->get($this->keyPrefix . $key);
        
        if (false !== $value) {
            return $value;
        } else {
            return null;
        }
    }

    /**
     * Returns the type of the cache adapter.
     *
     * @return string Type of the cache.
     */
    public function getType()
    {
        return $this->config['type'];
    }
    
    /**
     * Checks if an entry is cached.
     *
     * @param string $key ID of the entry to check.
     * @return boolean True, if entry behind given $key exists, false otherwise.
     */
    public function isCached($key)
    {
        return false !== $this->cache->get($this->keyPrefix . $key);
    }

    /**
     * Stores a new entry in the cache or overrides an existing one.
     *
     * @param string $key Identifier of the value to store.
     * @param mixed $value Value to store in the cache.
     */
    public function set($key, $value)
    {
        $this->cache->set($this->keyPrefix . $key, $value);
    }

    /**
     * Setup cache adapter. All operations to establish a connection to the cache have to be done.
     *
     * @param array $config Array containing necessary parameter to setup a cache adapter.
     * @throws \Exception If one requirement is not fullfilled.
     */
    public function setup(array $config)
    {
        if (true === $this->checkRequirements()) {
            $this->cache = new \Memcached('Saft\Cache');
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
        }
    }
}
