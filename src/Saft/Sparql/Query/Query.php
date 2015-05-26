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
