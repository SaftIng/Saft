<?php

namespace Saft\Sparql\Query;

interface QueryFactory
{
    /**
     * Creates an instance of Query based on given query string.
     *
     * @param  string $query SPARQL query string to use for class instantiation.
     * @return Query Instance of Query.
     */
    public function createInstanceByQueryString($query);
}
