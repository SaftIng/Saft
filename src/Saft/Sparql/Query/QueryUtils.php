<?php

namespace Saft\Sparql\Query;

use Saft\Rdf\RdfHelpers;

/**
 * @deprecated Use Saft/Rdf/RdfHelpers!
 */
class QueryUtils
{
    protected $rdfHelpers;

    public function __construct()
    {
        $this->rdfHelpers = new RdfHelpers();
    }

    /**
     * Get type for a given SPARQL query.
     *
     * @param string $query
     * @return string Type, which is either askQuery, describeQuery, graphQuery, updateQuery or selectQuery
     * @throws \Exception if unknown query type.
     * @deprecated Use Saft/Rdf/RdfHelpers!
     */
    public function getQueryType($query)
    {
        return $this->rdfHelpers->getQueryType($query);
    }
}
