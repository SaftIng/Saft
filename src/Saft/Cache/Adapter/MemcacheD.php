<?php

namespace Saft\Cache\Adapter;

class MemcacheD extends \Saft\Cache\Adapter\AbstractAdapter
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
     * Removes all cached entries.
     *
     * @return void
     */
    public function clean()
    {
        return $this->cache->flush();
    }

    /**
     * Deletes a certain entry.
     *
     * @param  string $key ID of the entry to delete.
     * @return void
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
        return $this->cache->get($this->keyPrefix . $key);
    }

    /**
     * Returns the type of the cache adapter.
     *
     * @return string Type of the cache.
     */
    public function getType()
    {
        return $this->config["type"];
    }

    /**
     * Stores a new entry in the cache or overrides an existing one.
     *
     * @param  string $key   Identifier of the value to store.
     * @param  mixed  $value Value to store in the cache.
     * @return void
     */
    public function set($key, $value)
    {
        $this->cache->set($this->keyPrefix . $key, $value);
    }

    /**
     * Setup MemcacheD cache adapter.
     *
     * @param array $config Array containing necessary parameter to setup the
     *                      server.
     * @throw \Enable\Exception
     */
    public function setup(array $config)
    {
        if (true === class_exists("\Memcached") && true === extension_loaded("memcached")) {
            $this->cache = new \Memcached("Enable\Cache");
            $servers = $this->cache->getServerList();

            // check if the host-port-combination is already in the server list,
            // to avoid adding the same configuration multiple times
            if (true === empty($servers)) {
                $this->cache->setOption(\Memcached::OPT_RECV_TIMEOUT, 1000);
                $this->cache->setOption(\Memcached::OPT_SEND_TIMEOUT, 3000);
                $this->cache->setOption(\Memcached::OPT_TCP_NODELAY, true);
                $this->cache->addServer(
                    $config["host"],
                    $config["port"]
                );
            }

            $this->config = $config;

        } else {
            throw new \Exception("Memcached extension is not available.");
        }
    }
}
