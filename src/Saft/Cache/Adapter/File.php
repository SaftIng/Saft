<?php

namespace Saft\Cache\Adapter;

class File extends \Saft\Cache\Adapter\AbstractAdapter
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
     *
     * @param
     * @return
     * @throw
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
     *
     * @param
     * @return
     * @throw
     */
    public function delete($key)
    {
        $filename = hash("sha256", $key);

        if (true === $this->isCached($key)) {
            unlink($this->cacheDir . $filename .".cache");
        }
    }

    /**
     *
     * @param
     * @return
     * @throw
     */
    public function get($key)
    {
        $filename = hash("sha256", $key);

        if (true === $this->isCached($key)) {
            return json_decode(
                file_get_contents($this->cacheDir . $filename . ".cache"),
                true
            );
        } else {
            return false;
        }
    }

    /**
     * @return string Type of the cache.
     */
    public function getType()
    {
        return $this->_config["type"];
    }

    /**
     * @param string $key ID of the file to check.
     * @return boolean True, if file behind given $key exists, false otherwise.
     */
    protected function isCached($key)
    {
        $filename = hash("sha256", $key);

        return true === file_exists($this->cacheDir . $filename .".cache");
    }

    /**
     * @param string $key   ID of the value to store.
     * @param mixed  $value Value to store.
     * @return
     * @throw
     */
    public function set($key, $value)
    {
        $filename = hash("sha256", $key);
        $value = json_encode($value);

        file_put_contents($this->cacheDir . $filename .".cache", $value);
    }

    /**
     * Setup File cache adapter
     *
     * @param array $config Array containing necessary parameter to setup the
     *                      server.
     * @throw \Enable\Exception
     */
    public function setup(array $config)
    {
        // save reference to systems temp directory
        $this->tempDir = sys_get_temp_dir();

        if (true === is_readable($this->tempDir)
            && true === is_writable($this->tempDir)
        ) {
            $this->cacheDir = $this->tempDir . "/enable/";

            try {
                // if caching folder does not exists, create it
                if (false === file_exists($this->cacheDir)) {
                    mkdir($this->cacheDir, 0744);
                }
            } catch (\Exception $e) {
                throw new \Enable\Exception($e->getMessage());
            }

            $this->_config = $config;

        } else {
            throw new \Enable\Exception(
                "Systems temporary folder is either not readable or writable."
            );
        }
    }
}
