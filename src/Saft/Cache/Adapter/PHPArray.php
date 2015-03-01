<?php

namespace Saft\Cache\Adapter;

class PHPArray extends \Saft\Cache\Adapter\AbstractAdapter
{
    /**
     * @var string
     */
    protected $cacheDir;

    /**
     * Checks that all requirements for this adapter are fullfilled.
     *
     * @return boolean Returns true if all requirements are fullfilled.
     * @throws \Exception If one requirement is not fullfilled an exception will be thrown.
     */
    public function checkRequirements()
    {
        // no requirements to fullfill.

        return true;
    }

    /**
     * Clears cache array.
     */
    public function clean()
    {
        $this->cache = array();
    }

    /**
     * Deletes entry by given $key.
     *
     * @param string $key
     */
    public function delete($key)
    {
        unset($this->cache[$key]);
    }

    /**
     *
     * @param string $key
     * @return mixed|null
     * @throw
     */
    public function get($key)
    {
        if (true === $this->isCached($key)) {
            return json_decode($this->cache[$key], true);
        } else {
            return false;
        }
    }

    /**
     * @return string Type of the cache.
     */
    public function getType()
    {
        return "phparray";
    }

    /**
     * Check if key is cached.
     *
     * @param  string $key ID of the array entry.
     * @return boolean True, if entry behind given $key exists, false otherwise.
     */
    protected function isCached($key)
    {
        return true === isset($this->cache[$key]);
    }

    /**
     * @param string $key   ID of the value to store.
     * @param mixed  $value Value to store.
     */
    public function set($key, $value)
    {
        $this->cache[$key] = json_encode($value);
    }

    /**
     * Setup cache adapter
     *
     * @param array $config Array containing necessary parameter to init instance.
     * @throw \Exception
     */
    public function init(array $config)
    {
        $this->cache = array();
        $this->config = $config;
    }

    /**
     *
     */
    public function setup()
    {
    }
}
