<?php

namespace Saft\Sparql\Query;

use Saft\Sparql\Query\QueryUtils;

class QueryFactoryImpl implements QueryFactory
{
    /**
     * @var QueryUtils
     */
    protected $queryUtils;

    public function __construct()
    {
        $this->queryUtils = new QueryUtils();
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
                return new AskQueryImpl($query);

            case 'constructQuery':
                return new ConstructQueryImpl($query);

            case 'describeQuery':
                return new DescribeQueryImpl($query);

            case 'graphQuery':
                return new GraphQueryImpl($query);

            case 'selectQuery':
                return new SelectQueryImpl($query);

            case 'updateQuery':
                return new UpdateQueryImpl($query);

            default:
                throw new \Exception('Unknown query type: '. $query);
        }
    }
}
