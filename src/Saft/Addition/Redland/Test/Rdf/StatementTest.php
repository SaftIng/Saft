<?php

namespace Saft\Addition\Redland\Tests\Rdf;

use Saft\Rdf\AnyPatternImpl;
use Saft\Addition\Redland\Rdf\BlankNode;
use Saft\Addition\Redland\Rdf\Literal;
use Saft\Addition\Redland\Rdf\NamedNode;
use Saft\Addition\Redland\Rdf\NodeFactory;
use Saft\Addition\Redland\Rdf\Statement;

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
        $world = librdf_php_get_world();

        $factory = new NodeFactory();
        try {
            $redlandSubject = $factory->createRedlandNodeFromNode($subject);
            $redlandPredicate = $factory->createRedlandNodeFromNode($predicate);
            $redlandObject = $factory->createRedlandNodeFromNode($object);

            $statement = librdf_new_statement_from_nodes($world, $redlandSubject, $redlandPredicate, $redlandObject);
            return new Statement($statement, $graph);
        } catch (\Exception $e) {
            $this->markTestSkipped('Can\'t execute this test because: ' . $e->getMessage());
        }
    }

    /**
     * Check for reland extension to be installed before execute a test.
     */
    public function setUp()
    {
        if (false === extension_loaded('redland')) {
            $this->markTestSkipped('Extension redland is not loaded.');
        }

        parent::setUp();
    }
}
