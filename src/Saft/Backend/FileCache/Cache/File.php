<?php

namespace Saft\Backend\FileCache\Cache;

use Saft\Cache\Cache;
use Saft\Cache\CacheInterface;

class File implements CacheInterface
{
    /**
     * @var string
     */
    protected $cachePath;

    /**
     * Checks that all requirements for this adapter are fullfilled.
     *
     * @return boolean Returns true if all requirements are fullfilled, false otherwise
     * @throws \Exception If one requirement is not fullfilled.
     */
    public function checkRequirements()
    {
        if (true === is_readable($this->cachePath) && true === is_writable($this->cachePath)) {
            return true;
        } else {
            throw new \Exception('Cache folder is either not readable or writable.');
        }
    }

    /**
     * Removes all entries of the cache instance.
     */
    public function clean()
    {
        $dir = new \DirectoryIterator($this->cachePath);
        foreach ($dir as $fileinfo) {
            if (!$fileinfo->isDot()) {
                unlink($this->cachePath . $fileinfo->getFilename());
            }
        }
    }

    /**
     * Deletes a certain cache entry by key.
     *
     * @param string $key Key of the cache entry to delete.
     */
    public function delete($key)
    {
        $filename = hash('sha256', $key);

        if (true === $this->isCached($key)) {
            unlink($this->cachePath . $filename .'.cache');
        }
    }

    /**
     * Returns the value to a given key, if it exists in the cache.
     *
     * @param string $key ID of the entry to return the value from.
     * @return mixed Value of the entry. Returns null if there is no cache entry.
     */
    public function get($key)
    {
        $entry = $this->getCompleteEntry($key);
        return null !== $entry ? $entry['value'] : null;
    }

    /**
     * Returns the complete cache entry to a given key, if it exists in the cache.
     *
     * @param string $key ID of the entry to return the value from.
     * @return mixed Value of the entry. Returns null if there is no cache entry.
     */
    public function getCompleteEntry($key)
    {
        $filename = hash('sha256', $key);

        if (true === $this->isCached($key)) {
            // load content from cache file and decode it
            $encodedContainer = file_get_contents($this->cachePath . $filename . '.cache');

            $container = json_decode($encodedContainer, true);

            /**
             * Store meta data
             */
            ++$container['get_count'];

            // save adapted $container
            $encodedContainer = json_encode($container);
            file_put_contents($this->cachePath . $filename .'.cache', $encodedContainer);

            // unserialize value, if it is serialized
            if (true === Cache::isSerialized($container['value'])) {
                $container['value'] = unserialize($container['value']);
            }

            return $container;

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
        $filename = hash('sha256', $key);

        return true === file_exists($this->cachePath . $filename .'.cache');
    }

    /**
     * Stores a new entry in the cache or overrides an existing one. Further information to that new entry
     * will be stored.
     *
     * @param string $key Identifier of the value to store.
     * @param mixed $value Content to store in the cache.
     */
    public function set($key, $value)
    {
        $filename = hash('sha256', $key);

        $value = serialize($value);

        if (true === $this->isCached($key)) {
            $content = file_get_contents($this->cachePath . $filename . '.cache');
            $container = json_decode($content, true);

            $container['value'] = $value;

            ++$container['set_count'];
        } else {
            $container = array();
            $container['get_count'] = 0;
            $container['set_count'] = 1;
            $container['value'] = $value;
        }

        $encodedContainer = json_encode($container);

        file_put_contents($this->cachePath . $filename .'.cache', $encodedContainer);
    }

    /**
     * Setup cache adapter. All operations to establish a connection to the cache have to be done. It should
     * call checkRequirements to be sure all requirements are fullfilled, before init anything.
     *
     * @param array $config Array containing necessary parameter to setup a cache adapter.
     * @throws \Exception If cache dir is not read- or writeable.
     */
    public function setup(array $config)
    {
        // if cachePath key is not set, save reference to systems temp directory
        if (false === isset($config['cachePath'])) {
            $this->cachePath = sys_get_temp_dir() . '/saft/';
        } else {
            $this->cachePath = $config['cachePath'];

            // check that there is a / at the end
            if ('/' !== substr($this->cachePath, strlen($this->cachePath)-1, 1)) {
                $this->cachePath .= '/';
            }
        }

        if (!file_exists($this->cachePath)) {
            mkdir($this->cachePath);
        }

        // init, if requirements are fullfilled
        if (true === $this->checkRequirements()) {
            $this->config = $config;
        }
    }
}
