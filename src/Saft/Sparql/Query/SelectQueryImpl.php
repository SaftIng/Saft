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

use Saft\Rdf\RdfHelpers;

/**
 * Represents a SELECT query.
 */
class SelectQueryImpl extends AbstractQuery
{
    /**
     * Constructor.
     *
     * @param string optional $query SPARQL query string to initialize this instance
     */
    public function __construct($query = '', RdfHelpers $rdfHelpers)
    {
        parent::__construct($query, $rdfHelpers);

        if (null === $this->query) {
            return;
        }

        $parts = [
            'select' => [],
            'from' => [],
            'from_named' => [],
            'where' => [],
            'order' => [],
            'limit' => [],
            'offset' => [],
        ];

        // regex for variables
        $var = '[?$]{1}[\w\d]+';

        $tokens = [
            'select' => '/('.
                            // SELECT part
                            '((SELECT(\s)+)(DISTINCT(\s)+)'.

                            // COUNT
                            '?(COUNT(\s)*(\(.*?\)(\s)))?)(\?\w+\s+|\*)*'.

                            // LANG
                            '(\(LANG\(\?[a-zA-Z0-9\_]+\)\)* as{1}\s\?[a-zA-Z0-9\_]+)*'.

                            ')/si',
            'from' => '/FROM\s+<(.+?)>/i',
            'from_named' => '/FROM\s+NAMED\s+<(.+?)>/i',
            'where' => '/(WHERE\s+)?\{.*\}/si',
            'order' => '/ORDER\s+BY((\s+'.$var.'|\s+(ASC|DESC)\s*\(\s*'.$var.'\s*\))+)/i',
            'limit' => '/LIMIT\s+(\d+)/i',
            'offset' => '/OFFSET\s+(\d+)/i',
        ];

        foreach ($tokens as $key => $pattern) {
            preg_match_all($pattern, $query, $parts[$key]);
        }

        if (isset($parts['select'][0][0])) {
            $this->queryParts['select'] = trim($parts['select'][0][0]);
        }

        /*
         * FROM
         */
        if (isset($parts['from'][1][0])) {
            $this->queryParts['graphs'] = $parts['from'][1];
        }

        /*
         * FROM NAMED
         */
        if (isset($parts['from_named'][1][0])) {
            $this->queryParts['named_graphs'] = $parts['from_named'][1];
        }

        /*
         * WHERE
         */
        if (isset($parts['where'][0][0])) {
            $this->queryParts['where'] = $parts['where'][0][0];
        }

        /*
         * ORDER BY
         */
        if (isset($parts['order'][1][0])) {
            $this->queryParts['order'] = trim($parts['order'][1][0]);
        }

        /*
         * LIMIT
         */
        if (isset($parts['limit'][1][0])) {
            $this->queryParts['limit'] = $parts['limit'][1][0];
        }

        /*
         * OFFSET
         */
        if (isset($parts['offset'][1][0])) {
            $this->queryParts['offset'] = $parts['offset'][1][0];
        }
    }

    public function __toString()
    {
        $queryString = $this->queryParts['select'].' '.PHP_EOL;

        if (true === isset($this->queryParts['graphs'])) {
            foreach (array_unique($this->queryParts['graphs']) as $graphUri) {
                $queryString .= 'FROM <'.$graphUri.'>'.PHP_EOL;
            }
        }

        if (true === isset($this->queryParts['named_graphs'])) {
            foreach (array_unique($this->queryParts['named_graphs']) as $graphUri) {
                $queryString .= 'FROM NAMED <'.$graphUri.'>'.PHP_EOL;
            }
        }

        $queryString .= $this->queryParts['where'].' ';

        if (true === isset($this->queryParts['order'])) {
            $queryString .= 'ORDER BY '.$this->queryParts['order'].PHP_EOL;
        }

        if (true === isset($this->queryParts['limit'])) {
            $queryString .= 'LIMIT '.$this->queryParts['limit'].PHP_EOL;
        }

        if (true === isset($this->queryParts['offset'])) {
            $queryString .= 'OFFSET '.$this->queryParts['offset'].PHP_EOL;
        }

        return $queryString;
    }

    /**
     * Return parts of the query on which this instance based on.
     *
     * @return array $queryParts query parts; parts which have no elements will be unset
     */
    public function getQueryParts()
    {
        $this->queryParts['filter_pattern'] = $this->extractFilterPattern($this->queryParts['where']);
        $this->queryParts['graphs'] = $this->extractGraphs($this->getQuery());
        $this->queryParts['named_graphs'] = $this->extractNamedGraphs($this->getQuery());
        $this->queryParts['namespaces'] = $this->extractNamespacesFromQuery($this->queryParts['where']);
        $this->queryParts['prefixes'] = $this->extractPrefixesFromQuery($this->getQuery());
        // extract variables only from SELECT part
        $this->queryParts['result_variables'] = $this->extractVariablesFromQuery($this->queryParts['select']);
        $this->queryParts['triple_pattern'] = $this->extractTriplePattern($this->queryParts['where']);
        $this->queryParts['variables'] = $this->extractVariablesFromQuery($this->getQuery());

        $this->unsetEmptyValues($this->queryParts);

        return $this->queryParts;
    }

    /**
     * Is instance of AskQuery?
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
     * Is instance of DescribeQuery?
     *
     * @return bool False
     */
    public function isDescribeQuery()
    {
        return false;
    }

    /**
     * Is instance of GraphQuery?
     *
     * @return bool False
     */
    public function isGraphQuery()
    {
        return false;
    }

    /**
     * Is instance of SelectQuery?
     *
     * @return bool True
     */
    public function isSelectQuery()
    {
        return true;
    }

    /**
     * Is instance of UpdateQuery?
     *
     * @return bool False
     */
    public function isUpdateQuery()
    {
        return false;
    }
}
