<?php

namespace Saft\Store;

/**
 * Predefined sparql Store. All Triple methods reroute to the query-method.
 * In the specific sparql-Store those no longer have to be implemented,
 * but only the Query method / SPARQL interpreter itself.
 */
abstract class AbstractSparqlStore implements StoreInterface
{
    public function addStatements(\Saft\Rdf\StatementIterator $statements, $graphUri = null, array $options = array())
    {
        foreach ($statements as $st) {
            if ($st instanceof Statement) {
                if (!$st->isConcrete()) {
                    throw new \Exception("at least one Statement is not concrete");
                }
            }
        }
        $query = "Insert DATA\n"
            . "{\n";

        //TODO eliminate redundancy
        $query = $query . $this->sparqlFormat($statements, $graphUri) . "}";
        if (is_callable($this, 'query')) {
            return $this->query($query);
        } else {
            return $query;
        }
    }

    public function deleteMatchingStatements(\Saft\Rdf\Statement $statement, $graphUri = null, array $options = array())
    {
        $query = "Delete DATA\n"
            . "{\n";

        $query = $query . $this->sparqlFormat($statements, $graphUri) . "}";
        if (is_callable($this, 'query')) {
            return $this->query($query);
        } else {
            return $query;
        }
    }

    public function getMatchingStatements(\Saft\Rdf\Statement $Statement, $graphUri = null, array $options = array())
    {
        //TODO Filter Select
        $query = "Select * \n"
            ."WHERE\n"
            . "{\n";

        $query = $query . $this->sparqlFormat($statements, $graphUri) . "}";
        if (is_callable($this, 'query')) {
            return $this->query($query);
        } else {
            return $query;
        }
    }

    public function hasMatchingStatement(\Saft\Rdf\Statement $Statement, $graphUri = null, array $options = array())
    {
        $query = "ASK\n"
            . "{\n";

        $query = $query . $this->sparqlFormat($statements, $graphUri) . "}";
        if (is_callable($this, 'query')) {
            return $this->query($query);
        } else {
            return $query;
        }
    }

    /**
     * return the Statement-Data in sparql-Format.
     * @param \Saft\Sparql\StatementList $Statements
     * @param string                     $graphUri,  use if Statement is a triple and to using another graph when the default.
     * @return string, part of query
     */
    private function sparqlFormat(\Saft\Rdf\StatementIterator $Statements, $graphUri = null)
    {
        $query = '';
        foreach ($Statements as $st) {
            if ($st instanceof Statement) {
                $con = $st->toSparqlFormat();

                $graph = $st->getGraph();
                if (!is_null($graph)) {
                    $con = "Graph <" . $graphUri . "> {" . $con . "}";
                } elseif (!is_null($graphUri)) {
                    $con = "Graph <" . $graph . "> {" . $con . "}";
                }

                $query = $query . $con ."\n";
            }
        }
        unset($statement);
        return $query;
    }
}
