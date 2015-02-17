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
     * Checks that all requirements for this adapter are fullfilled. 
     * 
     * @return boolean Returns true if all requirements are fullfilled.
     * @throws \Exception If one requirement is not fullfilled an exception will be thrown.
     */
    public function checkRequirements()
    {
        if (false === class_exists("\Memcached") || false === extension_loaded("memcached")) {
            throw new \Exception("Memcached extension is not available or Memcached class does not exists.");
        } 
        
        return true;
    }
    
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
     * @param string $key ID of the entry to delete.
     * @return void
     */
    public function delete($key)
    {
        return $this->cache->delete($key);
    }
    
    /**
     * Returns the value of an entry, if it exists in the cache.
     * 
     * @param string $key ID of the entry.
     * @return mixed Value of the entry.
     */
    public function get($key)
    {
        return $this->cache->get($key);
    }  
    
    /**
     * Returns the type of the cache adapter.
     * 
     * @return string Type of the cache.
     */
    public function getType()
    {
        return "memcached";
    }
    
    /**
     * Init cache adapter. It should call checkRequirements to be sure all requirements
     * are fullfilled, before init anything.
     * 
     * @throws \Exception If checkRequirements is getting called, it can throw exceptions.
     */
    public function init(array $config)
    {
        $this->cache = new \Memcached("Enable\Cache");
        $servers = $this->cache->getServerList();
        
        // check if the host-port-combination is already in the server list,
        // to avoid adding the same configuration multiple times
        if(true === empty($servers)) {
            $this->cache->setOption(\Memcached::OPT_RECV_TIMEOUT, 1000);
            $this->cache->setOption(\Memcached::OPT_SEND_TIMEOUT, 3000);
            $this->cache->setOption(\Memcached::OPT_TCP_NODELAY, true);
            $this->cache->addServer(
                $config["host"], $config["port"]
            );
        }
        
        $this->_config = $config;
    } 
    
    /**
     * Stores a new entry in the cache or overrides an existing one.
     * 
     * @param string $key Identifier of the value to store.
     * @param mixed $value Value to store in the cache.
     * @return void
     */
    public function set($key, $value)
    {
        $this->cache->set($key, $value);
    }
}
