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

class QueryFactoryImpl implements QueryFactory
{
    /**
     * @var RdfHelpers
     */
    protected $rdfHelpers;

    public function __construct(RdfHelpers $rdfHelpers)
    {
        $this->rdfHelpers = $rdfHelpers;
    }

    /**
     * Creates an instance of Query based on given query string.
     *
     * @param  string $query SPARQL query string to use for class instantiation.
     * @return Query Instance of Query.
     */
    public function createInstanceByQueryString($query)
    {
        switch ($this->rdfHelpers->getQueryType($query)) {
            case 'askQuery':
                return new AskQueryImpl($query, $this->rdfHelpers);

            case 'constructQuery':
                return new ConstructQueryImpl($query, $this->rdfHelpers);

            case 'describeQuery':
                return new DescribeQueryImpl($query, $this->rdfHelpers);

            case 'graphQuery':
                return new GraphQueryImpl($query, $this->rdfHelpers);

            case 'selectQuery':
                return new SelectQueryImpl($query, $this->rdfHelpers);

            case 'updateQuery':
                return new UpdateQueryImpl($query, $this->rdfHelpers);

            default:
                throw new \Exception('Unknown query type: '. $query);
        }
    }
}
