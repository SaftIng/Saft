<?php

namespace Saft\Store\Adapter;

abstract class AbstractAdapter 
{
    /**
     * @var $config
     */
    protected $config;
    
    /**
     * Check requirements.
     */
    //public abstract function checkRequirements();
    
    /**
     * Connect to database or endpoint.
     */
    //public abstract function connect();
    
    /**
     * Closes existing connection.
     */
    //public abstract function disconnect();
    
    /**
     * Returns $config.
     * 
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }
    
    /**
     * Init adapter.
     */
    //public abstract function init(array $config);
    
    /**
     * Send SPARQL query to the server.
     * 
     * @param string $query Query to execute
     * @param array $variables optional Key-value-pairs to create prepared statements
     * @param array $options optional Options to configure the query-execution and the result.
     * @return array
     * @throw \Exception
     */
    //public abstract function sparql($query, array $options = array());
}
