<?php

namespace Saft\Sparql\Query;

use Saft\Rdf\NodeUtils;
use Saft\Sparql\Query\QueryUtils;

class QueryFactoryImpl implements QueryFactory
{
    /**
     * @var NodeUtils
     */
    protected $nodeUtils;

    /**
     * @var QueryUtils
     */
    protected $queryUtils;

    public function __construct(NodeUtils $nodeUtils, QueryUtils $queryUtils)
    {
        $this->nodeUtils = $nodeUtils;
        $this->queryUtils = $queryUtils;
    }

    /**
     * Creates an instance of Query based on given query string.
     *
     * @param  string $query SPARQL query string to use for class instantiation.
     * @return Query Instance of Query.
     */
    public function createInstanceByQueryString($query)
    {
        switch ($this->queryUtils->getQueryType($query)) {
            case 'askQuery':
                return new AskQueryImpl($query, $this->nodeUtils);

            case 'constructQuery':
                return new ConstructQueryImpl($query, $this->nodeUtils);

            case 'describeQuery':
                return new DescribeQueryImpl($query, $this->nodeUtils);

            case 'graphQuery':
                return new GraphQueryImpl($query, $this->nodeUtils);

            case 'selectQuery':
                return new SelectQueryImpl($query, $this->nodeUtils);

            case 'updateQuery':
                return new UpdateQueryImpl($query, $this->nodeUtils);

            default:
                throw new \Exception('Unknown query type: '. $query);
        }
    }
}
