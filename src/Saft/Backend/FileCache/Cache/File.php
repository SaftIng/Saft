<?php

namespace Saft\Backend\FileCache\Cache;

use Saft\Cache\CacheInterface;

class File implements CacheInterface
{
    /**
     * @var string
     */
    protected $cacheDir;

    /**
     * @var string
     */
    protected $tempDir;
    
    /**
     * Checks that all requirements for this adapter are fullfilled.
     *
     * @return boolean Returns true if all requirements are fullfilled, false otherwise
     * @throws \Exception If one requirement is not fullfilled.
     */
    public function checkRequirements()
    {
        if (true === is_readable($this->tempDir) && true === is_writable($this->tempDir)) {
            return true;
        } else {
            throw new \Enable\Exception(
                'Systems temporary folder is either not readable or writable.'
            );
        }
    }

    /**
     * Removes all entries of the cache instance.
     */
    public function clean()
    {
        $dir = new \DirectoryIterator($this->cacheDir);
        foreach ($dir as $fileinfo) {
            if (!$fileinfo->isDot()) {
                unlink($this->cacheDir . $fileinfo->getFilename());
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
            unlink($this->cacheDir . $filename .'.cache');
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
        $filename = hash('sha256', $key);

        if (true === $this->isCached($key)) {
            return json_decode(
                file_get_contents($this->cacheDir . $filename . '.cache'),
                true
            );
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
        return $this->_config['type'];
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

        return true === file_exists($this->cacheDir . $filename .'.cache');
    }

    /**
     * Stores a new entry in the cache or overrides an existing one.
     *
     * @param string $key Identifier of the value to store.
     * @param mixed $value Value to store in the cache.
     */
    public function set($key, $value)
    {
        $filename = hash('sha256', $key);
        $value = json_encode($value);

        file_put_contents($this->cacheDir . $filename .'.cache', $value);
    }

    /**
     * Setup cache adapter. All operations to establish a connection to the cache have to be done. It should
     * call checkRequirements to be sure all requirements are fullfilled, before init anything.
     *
     * @param array $config Array containing necessary parameter to setup a cache adapter.
     * @throws \Exception If one requirement is not fullfilled.
     * @todo support the use of user defined dir
     */
    public function setup(array $config)
    {
        // save reference to systems temp directory
        $this->tempDir = sys_get_temp_dir();

        if (true === $this->checkRequirements()) {
            $this->cacheDir = $this->tempDir . '/saft/';

            try {
                // if caching folder does not exists, create it
                if (false === file_exists($this->cacheDir)) {
                    mkdir($this->cacheDir, 0744);
                }
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage());
            }

            $this->_config = $config;
        }
    }
}
