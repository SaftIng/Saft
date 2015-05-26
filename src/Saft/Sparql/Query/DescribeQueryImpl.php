<?php

namespace Saft\Sparql\Query;

use Saft\Sparql\Query\AbstractQuery;

/**
 * Represents a DESCRIBE query.
 */
class DescribeQueryImpl extends AbstractQuery
{
    /**
     * Constructor.
     *
     * @param string optional $query SPARQL query string to initialize this instance.
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
        // extract the part before {
        $partBeforeBrace = substr($this->query, 0, strpos($this->query, '{'));

        $this->queryParts = array(
            'filter_pattern' => $this->extractFilterPattern($this->queryParts['where']),
            'graphs' => $this->extractGraphs($this->getQuery()),
            'named_graphs' => $this->extractNamedGraphs($this->getQuery()),
            'namespaces' => $this->extractNamespacesFromQuery($this->queryParts['where']),
            'prefixes' => $this->extractPrefixesFromQuery($this->getQuery()),
            'result_variables' => $this->extractVariablesFromQuery($partBeforeBrace),
            'sub_type' => $this->determineSubType($this->getQuery()),
            'triple_pattern' => $this->extractTriplePattern($this->queryParts['where']),
            'variables' => $this->extractVariablesFromQuery($this->getQuery())
        );

        $this->unsetEmptyValues($this->queryParts);

        return $this->queryParts;
    }

    /**
     *
     * @param string       $query
     * @return string|null
     */
    public function determineSubType($query)
    {
        $query = strtolower($query);

        if (false !== strpos($query, 'describe')) {
            // Check for e.g. DESCRIBE ?x WHERE { ... }
            if (false !== strpos($query, 'where')
                && false !== strpos($query, '{')
                && false !== strpos($query, '}')) {
                return 'describeWhere';

            // Assume its just e.g. DESCRIBE ?x
            } else {
                return 'describe';
            }

        } else {
            return null;
        }
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
     * @return boolean True
     */
    public function isDescribeQuery()
    {
        return true;
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
