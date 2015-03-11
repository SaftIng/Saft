<?php
namespace Saft\Rest;

use Saft\Store\StoreInterface;
use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\NamedNode;
use Saft\Rdf\Literal;
use Saft\Rdf\StatementImpl;

/**
 * @todo  add documentation
 * @todo  eliminate redundancy
 * @todo  statement-pattern missing
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
     * @return [type] [description]
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
                if (true === NamedNode::check($_POST['graphUri'])) {
                    $graphUri = new NamedNode($_POST['graphUri']);
                } else {
                    throw new \Exception('graphUri not a valid URI.');
                }
            }

            //TODO eliminate redundancy
            
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

            //deleteMatchingStatements
            } elseif ($this->method == 'DELETE') {
                if (is_array($statementsPost[0])) {
                    throw new \Exception('expect just one statement');
                }
                $statement = $this->createStatement(
                    $statementsPost[0],
                    $statementsPost[1],
                    $statementsPost[2],
                    $statementsPost[3]
                );
                return $this->store->deleteMatchingStatements($statement, $graphUri);

            //getMatchingStatements
            } elseif ($this->method == 'GET') {
                if (is_array($statementsPost[0])) {
                    throw new \Exception('expect just one statement');
                }
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
        } if ($this->verb == "store") {
            if ($this->method == 'GET') {
                //get Graphs
            }
        } else {
            return "Wrong input";
        }
    }

    private function createStatement($sub, $pred, $obj, $gr = null)
    {
        $subject = $this->createNode($sub);
        $predicate = $this->createNode($pred);
        $object = $this->createNode($obj);
        $graph = $this->createNode($gr);

        $statement = new StatementImpl($subject, $predicate, $object, $graph);
        return $statement;
    }

    private function createNode($value)
    {
        //TODO triple-pattern
        if (true === NamedNode::check($value)
            || null === $value) {
            return new NamedNode($value);
        } else {
            return new Literal($value);
        }
    }
}
