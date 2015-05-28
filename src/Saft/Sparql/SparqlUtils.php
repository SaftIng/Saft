<?php

namespace Saft\Sparql;

use Saft\Rdf\Node;
use Saft\Rdf\Statement;
use Saft\Rdf\StatementIterator;
use Saft\Rdf\StatementIteratorFactoryImpl;

class SparqlUtils
{
    /**
     * Returns the Statement-Data in sparql-Format.
     *
     * @param  StatementIterator|array $statements List of statements to format as SPARQL string.
     * @param  Node                    $graph      Use if each statement is a triple and to use another
     *                                             graph as the default.
     * @return string, part of query
     */
    public static function statementIteratorToSparqlFormat($statements, Node $graph = null)
    {
        $query = '';
        foreach ($statements as $statement) {
            if ($statement instanceof Statement) {
                $con = self::getNodeInSparqlFormat($statement->getSubject()) . ' ' .
                       self::getNodeInSparqlFormat($statement->getPredicate()) . ' ' .
                       self::getNodeInSparqlFormat($statement->getObject()) . ' . ';

                $graphToUse = $graph;
                if ($graph == null && $statement->isQuad()) {
                    $graphToUse = $statement->getGraph();
                }

                if (null !== $graphToUse) {
                    $sparqlString = 'Graph '. self::getNodeInSparqlFormat($graphToUse) .' {' . $con .'}';
                } else {
                    $sparqlString = $con;
                }

                $query .= $sparqlString .' ';
            } else {
                throw new \Exception('Not a Statement instance');
            }
        }
        return $query;
    }

    /**
     * Returns the Statement-Data in sparql-Format.
     *
     * @param  array  $statements List of statements to format as SPARQL string.
     * @param  string $graphUri   Use if each statement is a triple and to use another graph as the default.
     * @return string Part of query
     */
    public static function statementsToSparqlFormat(array $statements, Node $graph = null)
    {
        // TODO make it more flexible by move $factory to parameter list?
        $factory = new StatementIteratorFactoryImpl();
        $iterator = $factory->createIteratorFromArray($statements);
        return self::statementIteratorToSparqlFormat($iterator, $graph);
    }

    /**
     * Returns given Node instance in SPARQL format, which is in NQuads or as Variable
     *
     * @param  Node   $node Node instance to format.
     * @param  string $var The variablename, which should be used, if the node is not concrete
     * @return string Either NQuad notation (if node is concrete) or as variable.
     */
    public static function getNodeInSparqlFormat(Node $node, $var = null)
    {
        if ($node->isConcrete()) {
            return $node->toNQuads();
        }
        if ($var == null) {
            $var = uniqid('tempVar');
        }
        return '?' . $var;
    }
}
