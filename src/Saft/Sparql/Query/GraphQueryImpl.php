<?php

namespace Saft\Sparql\Query;

use Saft\Sparql\Query\AbstractQuery;

/**
 * Represents the following types of SPARQL queries:
 * - CLEAR GRAPH
 * - CREATE GRAPH
 * - CREATE SILENT GRAPH
 * - DROP GRAPH
 * - DROP SILENT GRAPH
 */
class GraphQueryImpl extends AbstractQuery
{
    /**
     *
     * @param string       $query
     * @return string|null
     */
    public function determineSubType($query)
    {
        /**
         * First we get rid of all PREFIX information
         */
        $adaptedQuery = preg_replace('/PREFIX\s+[a-z0-9]+\:\s*\<[a-z0-9\:\/\.\#\-]+\>/', '', $query);

        // remove trailing whitespaces
        $adaptedQuery = trim($adaptedQuery);

        // only lower chars
        $adaptedQuery = strtolower($adaptedQuery);

        $firstPart = substr($adaptedQuery, 0, 8);

        switch($firstPart) {
            // CLEAR GRAPH
            case 'clear gr':
                return 'clearGraph';

            // CREATE GRAPH
            case 'create g':
                return 'createGraph';

            // CREATE SILENT GRAPH
            case 'create s':
                return 'createSilentGraph';

            // DROP GRAPH
            case 'drop gra':
                return 'dropGraph';

            // DROP SILENT GRAPH
            case 'drop sil':
                return 'dropSilentGraph';

            default:
                return null;
        }
    }

    /**
     * Has no function here.
     *
     * @param  string $where WHERE part of the query.
     * @return array
     */
    public function extractFilterPattern($where)
    {
        return array();
    }

    /**
     * Extracts graph(s) from queryPart. Overrides parent method to ignore FROM part.
     *
     * @param  string $queryPart SPARQL query part.
     * @return array
     */
    public function extractGraphs($queryPart)
    {
        $graphs = array();

        $result = preg_match_all('/\\<(.*)\\>/', $queryPart, $matches);

        if (false !== $result && true === isset($matches[1][0])) {
            $graphs[] = $matches[1][0];
        }

        return $graphs;
    }

    /**
     * Has no function here.
     *
     * @param  string $query Query to get prefixes from.
     * @return array         List of extracted prefixes.
     */
    public function extractNamespacesFromQuery($query)
    {
        return array();
    }

    /**
     * Has no function here.
     *
     * @param  string $query Query to get prologue prefixes from.
     * @return array         List of extracted prefixes.
     */
    public function extractPrefixesFromQuery($query)
    {
        return array();
    }

    /**
     * Has no function here.
     *
     * @param  string $where WHERE part of the query.
     * @return array
     */
    public function extractTriplePattern($where)
    {
        return array();
    }

    /**
     * Has no function here.
     *
     * @param  string $query Query to get prefixes from.
     * @return array List of extracted variables.
     */
    public function extractVariablesFromQuery($query)
    {
        return array();
    }

    /**
     * Return parts of the query on which this instance based on. It overrides the parent function and sets
     * all values to null.
     *
     * @return array $queryParts Query parts; parts which have no elements or are unset will be marked with null.
    */
    public function getQueryParts()
    {
        $this->queryParts = array(
            'graphs' => $this->extractGraphs($this->getQuery()),
            'sub_type' => $this->determineSubType($this->getQuery()),
        );

        $this->unsetEmptyValues($this->queryParts);

        return $this->queryParts;
    }

    /**
     * Represents it an Ask Query?
     *
     * @return boolean False
     */
    public function isAskQuery()
    {
        return false;
    }

    /**
     * Represents it a Describe Query?
     *
     * @return boolean False
     */
    public function isDescribeQuery()
    {
        return false;
    }

    /**
     * Represents it a Graph Query?
     *
     * @return boolean True
     */
    public function isGraphQuery()
    {
        return true;
    }

    /**
     * Represents it a Select Query?
     *
     * @return boolean False
     */
    public function isSelectQuery()
    {
        return false;
    }

    /**
     * Represents it an Update Query?
     *
     * @return boolean False
     */
    public function isUpdateQuery()
    {
        return false;
    }
}
