<?php
namespace Saft\Rdf;

abstract class AbstractStatementIterator implements StatementIterator
{
    /**
     * May not be implemented
     */
    public function rewind()
    {
        // Nothing to do
    }

    /**
     * Returns the Statement-Data in sparql-Format.
     *
     * @param StatementIterator $statements   List of statements to format as SPARQL string.
     * @param string            $graphUri     Use if each statement is a triple and to use another graph as
     *                                        the default.
     * @return string String containing SPARQL formated statements.
     */
    public function toSparqlFormat($graphUri = null)
    {
        $string = '';
        foreach ($this as $st) {
            if ($st instanceof Statement) {
                $con = $st->toSparqlFormat();
                $graph = $st->getGraph();

                if (null !== $graphUri) {
                    $con = 'Graph <'. $graphUri .'> {'. $con .'}';
                } elseif (null !== $graph) {
                    $con = 'Graph <'. $graph->getUri() .'> {'. $con .'}';
                }
                $string .= $con .' ';
            } else {
                throw new \Exception('Not a Statement instance.');
            }
        }
        return $string;
    }
}
