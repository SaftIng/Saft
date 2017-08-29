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
