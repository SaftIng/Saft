<?php

namespace Saft\Sparql\Query;

use Saft\Sparql\Query\AbstractQuery;

/**
 * Represents an ASK query.
 */
class AskQueryImpl extends AbstractQuery
{
    /**
     * Constructor.
     *
     * @param  string     optional $query SPARQL query string to initialize this instance.
     * @throws \Exception If no where part was found in query.
     */
    public function __construct($query = '')
    {
        parent::__construct($query);

        if (null !== $this->query) {
            /*
             * Set where part
             */
            $result = preg_match('/\{(.*)\}/s', $query, $match);
            if (false !== $result && true === isset($match[1])) {
                $this->queryParts['where'] = trim($match[1]);
            } else {
                throw new \Exception('No where part found in query: '. $query);
            }
        }
    }

    /**
     * Return parts of the query on which this instance based on.
     *
     * @return array $queryParts Query parts; parts which have no elements will be unset.
    */
    public function getQueryParts()
    {
        // remove prefix information from query to be able to simply use extractGraphs on query string.
        $prefixlessQuery = preg_replace(
            '/PREFIX\s+([a-z0-9]+)\:\s*\<([a-z0-9\:\/\.\#\-]+)\>/',
            '',
            $this->getQuery()
        );

        $this->queryParts['filter_pattern'] = $this->extractFilterPattern($this->queryParts['where']);
        $this->queryParts['graphs'] = $this->extractGraphs($prefixlessQuery);
        $this->queryParts['namespaces'] = $this->extractNamespacesFromQuery($this->queryParts['where']);
        $this->queryParts['prefixes'] = $this->extractPrefixesFromQuery($this->getQuery());
        $this->queryParts['quad_pattern'] = $this->extractQuads($this->queryParts['where']);
        $this->queryParts['triple_pattern'] = $this->extractTriplePattern($this->queryParts['where']);
        $this->queryParts['variables'] = $this->extractVariablesFromQuery($this->getQuery());

        $this->unsetEmptyValues($this->queryParts);

        return $this->queryParts;
    }

    /**
     * Represents it an ASK query?
     *
     * @return boolean True
     */
    public function isAskQuery()
    {
        return true;
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
     * @return boolean False
     */
    public function isGraphQuery()
    {
        return false;
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
