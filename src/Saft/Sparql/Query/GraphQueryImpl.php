<?php

/*
 * This file is part of Saft.
 *
 * (c) Konrad Abicht <hi@inspirito.de>
 * (c) Natanael Arndt <arndt@informatik.uni-leipzig.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Saft\Sparql\Query;

/**
 * Represents the following types of SPARQL queries:
 * - CLEAR GRAPH
 * - CREATE GRAPH
 * - CREATE SILENT GRAPH
 * - DROP GRAPH
 * - DROP SILENT GRAPH.
 */
class GraphQueryImpl extends AbstractQuery
{
    /**
     * @param string $query
     *
     * @return string|null
     */
    public function determineSubType($query)
    {
        /**
         * First we get rid of all PREFIX information.
         */
        $adaptedQuery = preg_replace('/PREFIX\s+[a-z0-9]+\:\s*\<[a-z0-9\:\/\.\#\-]+\>/', '', $query);

        // remove trailing whitespaces
        $adaptedQuery = trim($adaptedQuery);

        // only lower chars
        $adaptedQuery = strtolower($adaptedQuery);

        $firstPart = substr($adaptedQuery, 0, 8);

        switch ($firstPart) {
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
     * @param string $where WHERE part of the query
     *
     * @return array
     */
    public function extractFilterPattern($where)
    {
        return [];
    }

    /**
     * Extracts graph(s) from queryPart. Overrides parent method to ignore FROM part.
     *
     * @param string $queryPart SPARQL query part
     *
     * @return array
     */
    public function extractGraphs($queryPart)
    {
        $graphs = [];

        $result = preg_match_all('/\\<(.*)\\>/', $queryPart, $matches);

        if (false !== $result && true === isset($matches[1][0])) {
            $graphs[] = $matches[1][0];
        }

        return $graphs;
    }

    /**
     * Has no function here.
     *
     * @param string $query query to get prefixes from
     *
     * @return array list of extracted prefixes
     */
    public function extractNamespacesFromQuery($query)
    {
        return [];
    }

    /**
     * Has no function here.
     *
     * @param string $query query to get prologue prefixes from
     *
     * @return array list of extracted prefixes
     */
    public function extractPrefixesFromQuery($query)
    {
        return [];
    }

    /**
     * Has no function here.
     *
     * @param string $where WHERE part of the query
     *
     * @return array
     */
    public function extractTriplePattern($where)
    {
        return [];
    }

    /**
     * Has no function here.
     *
     * @param string $query query to get prefixes from
     *
     * @return array list of extracted variables
     */
    public function extractVariablesFromQuery($query)
    {
        return [];
    }

    /**
     * Return parts of the query on which this instance based on. It overrides the parent function and sets
     * all values to null.
     *
     * @return array $queryParts query parts; parts which have no elements or are unset will be marked with null
     */
    public function getQueryParts()
    {
        $this->queryParts = [
            'graphs' => $this->extractGraphs($this->getQuery()),
            'sub_type' => $this->determineSubType($this->getQuery()),
        ];

        $this->unsetEmptyValues($this->queryParts);

        return $this->queryParts;
    }

    /**
     * Represents it an Ask Query?
     *
     * @return bool False
     */
    public function isAskQuery()
    {
        return false;
    }

    /**
     * Represents it a CONSTRUCT query?
     *
     * @return bool False
     */
    public function isConstructQuery()
    {
        return false;
    }

    /**
     * Represents it a Describe Query?
     *
     * @return bool False
     */
    public function isDescribeQuery()
    {
        return false;
    }

    /**
     * Represents it a Graph Query?
     *
     * @return bool True
     */
    public function isGraphQuery()
    {
        return true;
    }

    /**
     * Represents it a Select Query?
     *
     * @return bool False
     */
    public function isSelectQuery()
    {
        return false;
    }

    /**
     * Represents it an Update Query?
     *
     * @return bool False
     */
    public function isUpdateQuery()
    {
        return false;
    }
}
