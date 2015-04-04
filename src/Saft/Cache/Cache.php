<?php

namespace Saft\Cache;

use Saft\Backend\MemcacheD\Cache\MemcacheD;
use Saft\Backend\PhpArrayCache\Cache\PHPArray;

/**
 * Manages the cache itself.
 */
class Cache
{
    /**
     * @var CacheInterface
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
        return $this->cache->get($key);
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
        return $this->cache->getCompleteEntry($key);
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
                    $this->cache = new \Saft\Backend\FileCache\Cache\File();
                    $this->cache->setup($config);
                    break;

                /**
                 * MemcacheD
                 */
                case 'memcached':
                    $this->cache = new MemcacheD();
                    $this->cache->setup($config);
                    break;

                /**
                 * PHPArray
                 */
                case 'phparray':
                    $this->cache = new PHPArray();
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
     * This program is free software. It comes without any warranty, to
     * the extent permitted by applicable law. You can redistribute it
     * and/or modify it under the terms of the Do What The Fuck You Want
     * To Public License, Version 2, as published by Sam Hocevar. See
     * http://sam.zoy.org/wtfpl/COPYING for more details.
     */ 

    /**
     * Tests if an input is valid PHP serialized string.
     *
     * Checks if a string is serialized using quick string manipulation to throw out obviously incorrect strings. 
     * Unserialize is then run on the string to perform the final verification.
     * 
     * Copied from https://gist.github.com/cs278/217091
     * 
     * We adapted it for our purposes.
     * 
     * @author      Chris Smith <code+php@chris.cs278.org>
     * @copyright   Copyright (c) 2009 Chris Smith (http://www.cs278.org/)
     * @license     http://sam.zoy.org/wtfpl/WTFPL
     * @param       string  $value  Value to test for serialized form
     * @param       mixed   $result Result of unserialize() of the $value
     * @return      boolean         True if $value is serialized data, otherwise false
     */
    public static function isSerialized($value)
    {
        // Bit of a give away this one
        if (false === is_string($value)) {
            return false;
        }

        // Serialized false, return true. unserialize() returns false on an
        // invalid string or it could return false if the string is serialized
        // false, eliminate that possibility.
        if ($value === 'b:0;') {
            return true;
        }

        $length = strlen($value);
        $end = '';

        switch ($value[0]) {
            case 's':
                if ('"' !== $value[$length - 2]) {
                    return false;
                }
            case 'b':
            case 'i':
            case 'd':
                // This looks odd but it is quicker than isset()ing
                $end .= ';';
            case 'a':
            case 'O':
                $end .= '}';

                if (':' !== $value[1]) {
                    return false;
                }

                switch ($value[2]){
                    case 0:
                    case 1:
                    case 2:
                    case 3:
                    case 4:
                    case 5:
                    case 6:
                    case 7:
                    case 8:
                    case 9:
                    break;

                    default:
                        return false;
                }
            case 'N':
                $end .= ';';

                if ($value[$length - 1] !== $end[0]){
                    return false;
                }
                break;

            default:
                return false;
        }

        return true;
    }

    /**
     * Stores a new entry in the cache or overrides an existing one.
     *
     * @param string $key Identifier of the value to store.
     * @param mixed $value Value to store in the cache.
     */
    public function set($key, $value)
    {
        $this->cache->set($key, $value);
    }
}
