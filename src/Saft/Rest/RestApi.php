<?php
namespace Saft\Rest;

use Saft\Store\StoreInterface;
use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\NamedNode;
use Saft\Rdf\Literal;
use Saft\Rdf\Variable;
use Saft\Rdf\StatementImpl;

/**
 * @todo  add documentation
 * @todo  eliminate redundancy
 * @todo  hasMatchingStatement missing
 */
class RestApi extends \Saft\Rest\RestAbstract
{
    public function __construct($request, $origin, StoreInterface $store)
    {
        parent::__construct($request, $store);
    }

    /**
     * Rest-Endpoint
     * @return mixed
     */
    protected function store()
    {
        if ($this->verb == "statements") {
            if (!isset($_POST['statementsarray'])) {
                throw new \Exception('no statements passed.');
            }
            $statementsPost = $_POST['statementsarray'];
            foreach ($statementsPost as $st) {
                if (!is_array($st)) {
                    if (sizeof($statementsPost) == 3) {
                        $statementsPost[3] = null;
                    } elseif (sizeof($statementsPost) != 4) {
                        throw new \Exception('wrong statements-format. not a triple an not a quad');
                    }
                }
            }
            $graphUri = null;
            if (isset($_POST['graphUri'])) {
                if (NamedNode::check($_POST['graphUri']) ||
                    '?' == substr($_POST['graphUri'], 0, 1)) {
                    $graphUri = $_POST['graphUri'];
                } else {
                    throw new \Exception('graphUri not a valid URI.');
                }
            }
            
            //AddStatements
            if ($this->method == 'POST') {
                $statements = array();
                $i = 0;
                foreach ($statementsPost as $st) {
                    if (sizeof($st) == 3) {
                        $statement = $this->createStatement($st[0], $st[1], $st[2]);
                    } elseif (sizeof($st) == 4) {
                        $statement = $this->createStatement($st[0], $st[1], $st[2], $st[3]);
                    } else {
                        throw new \Exception('wrong statements-format. not a triple an not a quad');
                    }
                    $statements[$i] = $statement;
                    $i++;
                }
                $statements = new ArrayStatementIteratorImpl($statements);
                return $this->store->addStatements($statements, $graphUri);

            } else {
                if (is_array($statementsPost[0])) {
                    throw new \Exception('expect just one statement');
                }
                //deleteMatchingStatements
                if ($this->method == 'DELETE') {
                    $statement = $this->createStatement(
                        $statementsPost[0],
                        $statementsPost[1],
                        $statementsPost[2],
                        $statementsPost[3]
                    );
                    return $this->store->deleteMatchingStatements($statement, $graphUri);

                //getMatchingStatements
                } elseif ($this->method == 'GET') {
                    $statement = $this->createStatement(
                        $statementsPost[0],
                        $statementsPost[1],
                        $statementsPost[2],
                        $statementsPost[3]
                    );
                    return $this->store->getMatchingStatements($statement, $graphUri);

                } else {
                    return "Only accepts POST/GET/DELETE requests";
                }
            }

        } elseif ($this->verb == "store") {
            if ($this->method == 'GET') {
                //get Graphs
            }
        } else {
            return "Wrong input";
        }
    }

    /**
     * Create a Statement.
     * @param  string $sub
     * @param  string $pred
     * @param  string $obj
     * @param  string $gr
     * @return Statement
     */
    private function createStatement($sub, $pred, $obj, $gr = null)
    {
        $subject = $this->createNode($sub);
        $predicate = $this->createNode($pred);
        $object = $this->createNode($obj);
        $graph = $this->createNode($gr);

        $statement = new StatementImpl($subject, $predicate, $object, $graph);
        return $statement;
    }

    /**
     * Create a Node from string.
     * @param  string $value value of Node
     * @return Node        return NamedNode, Variable oder Literal
     */
    private function createNode($value)
    {
        if (true === NamedNode::check($value)
            || null === $value) {
            return new NamedNode($value);
        } elseif ('?' == substr($value, 0, 1)) {
            return new Variable($value);
        } else {
            return new Literal($value);
        }
    }
}
