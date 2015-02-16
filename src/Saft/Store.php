<?php

namespace Saft;

class Store 
{
    /**
     * @var \Saft\Store\Adapter\AbstractAdapter
     */
    protected $adapter;
    
    /**
     * @var \Saft\Cache
     */
    protected $cache;
    
    /**
     * @var \Saft\QueryCache
     */
    protected $queryCache;
    
    /**
     * 
     */
    public function init(array $config, \Saft\Cache $cache)
    {
        $config = array_merge(array(
            /**
             * Adapters are provided by aditional packages.
             */
            "adapter" => "http",
            
            "dsn" => "",
            
            "username" => "",
            
            "password" => "",
            
        ), $config);
        
        switch ($config["adapter"]) {
            case "http":
                break;
                
            case "virtuoso":
                $this->adapter = new \Saft\Store\Adapter\Virtuoso();
                break;
        }
        
        $this->adapter->init($config);
        $this->cache = $cache;
    }
    
    /**
     * 
     * @param
     * @return
     * @throw
     */
    public function initQueryCache()
    {
        if (null === $this->queryCache) {
            $this->queryCache = new \Saft\QueryCache();
            $this->queryCache->init($this->cache);
        }
    }
        
    /**
     * Checks if query cache is available. If it is not available install Saft/querycache via composer.
     * 
     * @return boolean
     */
    public function isQueryCacheAvailable()
    {
        return true === class_exists("Saft\\QueryCache");
    }
    
    /**
     * Send SPARQL query to the server.
     * 
     * @param string $query Query to execute
     * @param array $options optional Options to configure the query-execution and the result.
     * @return array
     * @throw \Exception
     */
    public function sparql($query, array $options = array())
    {
        // if requested, bypass QueryCache
        if (true === isset($options["useQueryCache"]) 
            && false === $options["useQueryCache"]) {
            return $this->adapter->sparql($query, $options);
        }
        
        // if query cache package is available
        if (true === $this->isQueryCacheAvailable()) {
            $this->initQueryCache();
            
            $queryId = $this->queryCache->generateShortId($query);
            $queryResult = $this->cache->get($queryId);
                
            if (null === $queryResult) {
                // execute the query in the store, save the result and init cache entry
                $this->queryCache->rememberQueryResult(
                    $query, $this->adapter->sparql($query, $options)
                );
                $queryResult = $this->cache->get($queryId);
            }
            
            $queryResult = $queryResult["result"];
            
        // if query cache package is NOT available
        } else {
            $queryResult = $this->adapter->sparql($query, $options);
        }
        
        return $queryResult;
    }
}
