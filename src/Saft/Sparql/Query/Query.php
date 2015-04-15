<?php

namespace Saft\Sparql\Query;

interface Query
{
    /**
     * @return string
     */
    public function getQuery();
    
    /**
     * @return array
     */
    public function getQueryParts();
    
    /**
     * Init the query instance with a given SPARQL query string.
     *
     * @param string $query Query to use for initialization.
     */
    public function init($query);
    
    /**
     * Is instance of AskQuery?
     *
     * @return boolean
     */
    public function isAskQuery();
    
    /**
     * Is instance of DescribeQuery?
     *
     * @return boolean
     */
    public function isDescribeQuery();
    
    /**
     * Is instance of GraphQuery?
     *
     * @return boolean
     */
    public function isGraphQuery();
    
    /**
     * Is instance of SelectQuery?
     *
     * @return boolean
     */
    public function isSelectQuery();
    
    /**
     * Is instance of UpdateQuery?
     *
     * @return boolean
     */
    public function isUpdateQuery();
}
