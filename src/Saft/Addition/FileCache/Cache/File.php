<?php

namespace Saft\Addition\FileCache\Cache;

use Saft\Cache\Cache;

class File implements Cache
{
    /**
     * @var string
     */
    protected $cachePath;

    /**
     * @var array
     */
    private $config;

    /**
     * @var string
     */
    private $keyPrefix = '';

    /**
     * Setup cache adapter. All operations to establish a connection to the cache have to be done. It should
     * call checkRequirements to be sure all requirements are fullfilled, before init anything.
     *
     * @param  array $config Array containing necessary parameter to setup the cache adapter.
     * @throws \Exception If cache dir is not read- or writeable.
     */
    public function __construct(array $config)
    {
        // if cachePath key is not set, save reference to systems temp directory
        if (false === isset($config['cachePath'])) {
            $this->cachePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'saft' . DIRECTORY_SEPARATOR;
        } else {
            $this->cachePath = $config['cachePath'];

            // check that there is the direcory separator at the end
            if (DIRECTORY_SEPARATOR !== substr($this->cachePath, strlen($this->cachePath)-1, 1)) {
                $this->cachePath .= DIRECTORY_SEPARATOR;
            }
        }

        if (false === file_exists($this->cachePath)) {
            mkdir($this->cachePath);
        }

        $this->checkRequirements();

        $this->config = $config;

        // save key prefix if it was given
        if (isset($config['keyPrefix'])) {
            $this->keyPrefix = $config['keyPrefix'];
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
        if (true === is_readable($this->cachePath) && true === is_writable($this->cachePath)) {
            return true;
        } else {
            throw new \Exception('Cache folder is either not readable or writable.');
        }
    }

    /**
     * Removes all entries that where created by the File.php.
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
        $filename = $this->keyPrefix . hash('sha256', $key);

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
        $filename = $this->keyPrefix . hash('sha256', $key);

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

            $container['value'] = unserialize($container['value']);

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
        $filename = $this->keyPrefix . hash('sha256', $key);

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
        $filename = $this->keyPrefix . hash('sha256', $key);

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
}
