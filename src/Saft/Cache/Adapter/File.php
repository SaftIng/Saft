<?php

namespace Saft\Cache\Adapter;

class File extends \Saft\Cache\Adapter\AbstractAdapter
{
    /**
     * @var string
     */
    protected $cacheDir;
    
    /**
     * Removes all files in the cache dir.
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
     * Deletes entry by given $key.
     * 
     * @param string $key
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
     * @param string $key
     * @return mixed|null
     * @throw
     */
    public function get($key)
    {
        $filename = hash("sha256", $key);
        
        if (true === $this->isCached($key)) {
            return json_decode(
                file_get_contents($this->cacheDir . $filename . ".cache"), true
            );
        } else {
            return null;
        }
    }  
    
    /**
     * @return string Type of the cache.
     */
    public function getType()
    {
        return "file";
    }     
    
    /**
     * Check if key is cached.
     * 
     * @param string $key ID of the file to check.
     * @return boolean True, if file behind given $key exists, false otherwise.
     */
    protected function isCached($key)
    {
        $filename = hash("sha256", $key);
        
        return true === file_exists($this->cacheDir . $filename .".cache");
    }     
    
    /**
     * @param string $key ID of the value to store.
     * @param mixed $value Value to store.
     */
    public function set($key, $value)
    {
        $filename = hash("sha256", $key);
        $value = json_encode($value);
        
        file_put_contents($this->cacheDir . $filename .".cache", $value);
    }
    
    /**
     * Setup cache adapter
     * 
     * @param array $config Array containing necessary parameter to init instance.
     * @throw \Exception
     */
    public function init(array $config)
    {
        // save reference to systems temp directory
        $this->cacheDir = sys_get_temp_dir();
        
        if (true === is_readable($this->cacheDir) 
            && true === is_writable($this->cacheDir)) {
                
            $this->cacheDir .= "/saft-cache/";
                
            try {
                // if caching folder does not exists, create it
                if (false === file_exists($this->cacheDir)) {
                    mkdir($this->cacheDir, 0744);
                }
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage());
            }
            
            $this->config = $config;
            
        } else {
            throw new \Exception(
                "Systems temporary folder ". $this->cacheDir ." is either not readable or writable."
            );
        }
    }
}
