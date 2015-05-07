<?php
namespace Saft\Backend\Redland\Tests\Rdf;

use \Saft\Rdf\AnyPatternImpl;
use \Saft\Backend\Redland\Rdf\BlankNode;
use \Saft\Backend\Redland\Rdf\Literal;
use \Saft\Backend\Redland\Rdf\NamedNode;
use \Saft\Backend\Redland\Rdf\NodeFactory;
use \Saft\Backend\Redland\Rdf\Statement;

class StatementTest extends \Saft\Rdf\Test\StatementAbstractTest
{
    public function newLiteralInstance($value, $lang = null, $datatype = null)
    {
        $factory = new NodeFactory();
        return $factory->createLiteral($value, $lang, $datatype);
    }

    public function newNamedNodeInstance($uri)
    {
        $factory = new NodeFactory();
        return $factory->createNamedNode($uri);
    }

    public function newAnyPatternInstance($id)
    {
        return new AnyPatternImpl();
    }

    public function newBlankNodeInstance($blankId)
    {
        $factory = new NodeFactory();
        return $factory->createBlankNode($blankId);
    }

    public function newInstance($subject, $predicate, $object, $graph = null)
    {
        $world = librdf_new_world();

        $factory = new NodeFactory();
        try {
            $redlandSubject = $factory->createRedlandNodeFromNode($subject);
            $redlandPredicate = $factory->createRedlandNodeFromNode($predicate);
            $redlandObject = $factory->createRedlandNodeFromNode($object);

            $statement = librdf_new_statement_from_nodes($world, $redlandSubject, $redlandPredicate, $redlandObject);
            return new Statement($statement, $graph);
        } catch (\Exception $e) {
            $this->markTestSkipped("Can't execute this test because: " . $e->getMessage());
        }
    }

    /**
     * Check for reland extension to be installed before execute a test.
     */
    public function setUp()
    {
        if (false === function_exists('librdf_new_world')) {
            $this->markTestSkipped('Can not find librdf_new_world function, so it seems Redland is not installed.');
        }

        parent::setUp();
    }
}
