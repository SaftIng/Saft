<?php

namespace Saft\Store;

use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\AbstractNamedNode;
use Saft\Rdf\StatementImpl;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\LiteralImpl;
use Saft\Rdf\VariableImpl;
use \Saft\Sparql\Query;

/**
 * Predefined Pattern-statement Store. The Triple-methods need to be implemented in the specific statement-store.
 * The query method is defined in the abstract class and reroute to the triple-methods.
 */
abstract class AbstractTriplePatternStore implements StoreInterface
{

    /**
     * @param string $query   SPARQL query string.
     * @param string $options optional Further configurations.
     * @throws ?
     */
    public function query($query, array $options = array())
    {
        //@TODO
        $queryParser = new Query();
        $queryParser->init($query);
        //$queryParts = $queryParser->getQueryParts();
        $queryStatements = $queryParser->getTriplePatterns();
        $statement = $this->getStatement($queryStatements[0]);

        if (stristr($query, 'select')) {
            //redirect to getMatchingStatements-methode
            return $this->getMatchingStatements($statement);
        } elseif (stristr($query, 'delete')) {
            //redirect to deleteMatchingStatement-methode
            return $this->deleteMatchingStatements($statement);
        } elseif (stristr($query, 'insert')) {
            //redirect to addStatements-methode
            return $this->addStatements($this->getStatements($queryStatements));
        } elseif (stristr($query, 'ask')) {
            //redirect to hasMatchingStatement-methode
            return $this->hasMatchingStatement($statement);
        }
    }

    /**
     * create Statement from query.
     * @param array $queryParts the part of the query with the description of the statement.
     * @return Statement           Statement-object
     */
    protected function getStatement(array $queryParts)
    {
        $subject = $this->createNode($queryParts['s'], $queryParts['s_type']);
        $predicate = $this->createNode($queryParts['p'], $queryParts['p_type']);
        $object = $this->createNode($queryParts['o'], $queryParts['o_type']);
        $statement = new StatementImpl($subject, $predicate, $object);

        return $statement;
    }

    /**
     * create statements from query
     * @param  array $queryParts the part of the query with the description of the statements.
     * @return StatementIterator             Statements
     */
    protected function getStatements(array $queryParts)
    {
        $statements = new ArrayStatementIteratorImpl(array());
        foreach ($queryParts as $st) {
            $statements->append($this->getStatement($st));
        }
        return $statements;
    }

    /**
     * Create a Node from string.
     *
     * @param  string $value value of Node
     * @param  string $type type of Node, can be uri, var or literal
     * @return Node   Returns NamedNode, Variable or Literal
     */
    private function createNode($value, $type)
    {
        if ('uri' == $type) {
            return new NamedNodeImpl($value);
        } elseif ('var' == $type) {
            return new VariableImpl('?' . $value);
        } elseif ('typed-literal' == $type || 'literal' == $type) {
            return new LiteralImpl($value);
        }
    }
}
