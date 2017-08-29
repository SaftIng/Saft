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

namespace Saft\Sparql;

use Saft\Rdf\Node;
use Saft\Rdf\RdfHelpers;
use Saft\Rdf\Statement;
use Saft\Rdf\StatementIterator;
use Saft\Rdf\StatementIteratorFactory;
use Saft\Rdf\StatementIteratorFactoryImpl;

/**
 * @deprecated Use Saft/Rdf/RdfHelpers!
 */
class SparqlUtils
{
    /**
     * @var StatementIteratorFactory
     */
    protected $statementIteratorFactory;

    /**
     * @var RdfHelpers
     */
    protected $rdfHelpers;

    public function __construct(StatementIteratorFactory $statementIteratorFactory)
    {
        $this->rdfHelpers = new RdfHelpers($statementIteratorFactory);
    }

    /**
     * Returns the Statement-Data in sparql-Format.
     *
     * @param  StatementIterator|array $statements List of statements to format as SPARQL string.
     * @param  Node                    $graph      Use if each statement is a triple and to use another
     *                                             graph as the default.
     * @return string, part of query
     * @deprecated Use Saft/Rdf/RdfHelpers!
     */
    public function statementIteratorToSparqlFormat($statements, Node $graph = null)
    {
        return $this->rdfHelpers->statementIteratorToSparqlFormat($statements, $graph);
    }

    /**
     * Returns the Statement-Data in sparql-Format.
     *
     * @param  array  $statements List of statements to format as SPARQL string.
     * @param  string $graphUri   Use if each statement is a triple and to use another graph as the default.
     * @return string Part of query
     * @deprecated Use Saft/Rdf/RdfHelpers!
     */
    public function statementsToSparqlFormat(array $statements, Node $graph = null)
    {
        return $this->rdfHelpers->statementsToSparqlFormat($statements, $graph);
    }

    /**
     * Returns given Node instance in SPARQL format, which is in NQuads or as Variable
     *
     * @param  Node   $node Node instance to format.
     * @param  string $var The variablename, which should be used, if the node is not concrete
     * @return string Either NQuad notation (if node is concrete) or as variable.
     * @deprecated Use Saft/Rdf/RdfHelpers!
     */
    public function getNodeInSparqlFormat(Node $node, $var = null)
    {
        return $this->rdfHelpers->getNodeInSparqlFormat($node, $var);
    }
}
