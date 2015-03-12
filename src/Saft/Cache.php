<?php

namespace Saft;

/**
 * Manages the cache itself.
 */
class Cache
{
    /**
     * @var
     */
    protected $cache;
    
    /**
     * Standard key prefix for cache entry keys. You can set it to separate your entries from others to avoid
     * naming conflicts.
     *
     * @var string
     */
    protected $keyPrefix = '';

    /**
     * Constructor, which calls init function.
     *
     * @param array $config Array to configure the Cache instance.
     */
    public function __construct(array $config)
    {
        $this->init($config);
    }

    /**
     * Removes all entries of the cache instance.
     */
    public function clean()
    {
        $this->cache->clean();
    }

    /**
     * Deletes a certain cache entry by key.
     *
     * @param string $key Key of the cache entry to delete.
     */
    public function delete($key)
    {
        $this->cache->delete($key);
    }

    /**
     * Returns the value to a given key, if it exists in the cache.
     *
     * @param string $key ID of the entry to return the value from.
     * @return mixed Value of the entry.
     */
    public function get($key)
    {
        $entry = $this->cache->get($key);
        
        // increase access count by 1 and save entry
        if (null !== $entry) {
            ++$entry['access_count'];
            $this->cache->set($key, $entry);
            return $entry['value'];
        } else {
            return null;
        }
    }

    /**
     * Returns the current active cache instance.
     *
     * @return mixed Instance which implements \Saft\Cache\CacheInterface
     */
    public function getCacheObj()
    {
        return $this->cache;
    }
    
    /**
     * Returns the complete cache entry to a given key, if it exists in the cache. It does not change the
     * cache entry itself, such as the access_count.
     *
     * @param string $key ID of the cache entry to return.
     * @return array|null Cache entry array or null, if not available.
     */
    public function getCompleteEntry($key)
    {
        return $this->cache->get($key);
    }

    /**
     * Returns the type of the cache adapter.
     *
     * @return string Type of the cache adapter.
     */
    public function getType()
    {
        return $this->_config['type'];
    }

    /**
     * Initialize the cache.
     *
     * @param array $config Array to configure this instance.
     * @throw \Exception If an unknown cache backend was used.
     */
    public function init(array $config)
    {
        if (true === isset($config['type'])) {
            switch ($config['type']) {
                /**
                 * File
                 */
                case 'file':
                    $this->cache = new \Saft\Cache\Adapter\File();
                    $this->cache->setup($config);
                    break;

                /**
                 * MemcacheD
                 */
                case 'memcached':
                    $this->cache = new \Saft\Cache\Adapter\MemcacheD();
                    $this->cache->setup($config);
                    break;

                /**
                 * PHPArray
                 */
                case 'phparray':
                    $this->cache = new \Saft\Cache\Adapter\PHPArray();
                    $this->cache->setup($config);
                    break;

                default:
                    throw new \Exception('Unknown cache backend.');
                    break;
            }

        } else {
            throw new \Exception('Unknown cache backend.');
        }

        if (true === isset($config['keyPrefix']) && false === empty($config['keyPrefix'])) {
            $this->keyPrefix = $config['keyPrefix'];
        }
    
        $this->_config = $config;
    }

    /**
     * Stores a new entry in the cache or overrides an existing one.
     *
     * @param string $key Identifier of the value to store.
     * @param mixed $value Value to store in the cache.
     */
    public function set($key, $value)
    {
        $entry = array(
            'access_count' => 0,
            'value' => $value
        );
        
        $this->cache->set($key, $entry);
    }
}
