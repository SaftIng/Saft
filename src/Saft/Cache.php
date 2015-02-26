<?php

namespace Saft;

/**
 * Manages the cache itself.
 */
class Cache
{
    /**
     * @var instance which implements \Enable\Cache\Adapter\Base
     */
    protected $_cache;
    
    /**
     * @param array $config Array to configure the Cache instance.
     * @return void
     */
    public function __construct(array $config)
    {
        $this->init($config);
    }
    
    /**
     * Removes all cached entries.
     * 
     * @return void
     */
    public function clean()
    {
        $this->_cache->clean();
    }
    
    /**
     * Deletes a certain entry.
     * 
     * @param string $key ID of the entry to delete.
     * @return void
     */
    public function delete($key)
    {
        $this->_cache->delete($key);
    }
    
    /**
     * Returns the value of an entry, if it exists in the cache.
     * 
     * @param string $key ID of the entry.
     * @return mixed Value of the entry.
     */
    public function get($key)
    {
        return $this->_cache->get($key);
    }  
    
    /**
     * Returns the current active cache instance.
     * 
     * @return mixed Instance which implements \Enable\Cache\Adapter\Base inteface
     */
    public function getCacheObj()
    {
        return $this->_cache;
    }
    
    /**
     * Returns the type of the cache adapter.
     * 
     * @return string Type of the cache.
     */
    public function getType()
    {
        return $this->_config["type"];
    }     
    
    /**
     * Initialize the cache.
     * 
     * @param array $config Array to configure this instance.
     * @throw \Exception In case of an unknown cache backend
     */
    public function init(array $config)
    {
        if (true === isset($config["type"])) {
            
            switch ($config["type"]) {
                
                /**
                 * File
                 */
                case "file": 
                    $this->_cache = new \Saft\Cache\Adapter\File();
                    $this->_cache->setup($config);
                    break;
                
                /**
                 * MemcacheD
                 */
                case "memcached": 
                    $this->_cache = new \Saft\Cache\Adapter\MemcacheD();
                    $this->_cache->setup($config);
                    break;
                    
                /**
                 * PHPArray
                 */
                case "phparray": 
                    $this->_cache = new \Saft\Cache\Adapter\PHPArray();
                    $this->_cache->setup($config);
                    break;
                    
                default:
                    throw new \Exception("Unknown cache backend.");
                    break;
            }
        
        } else {
            throw new \Exception("Unknown cache backend.");
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
        $this->_cache->set($key, $value);
    }
}
