<?php
namespace Saft\Backend\Redland\Tests\Rdf;

class NamedNodeTest extends \Saft\Rdf\Test\NamedNodeAbstractTest
{
    public function newInstance($uri)
    {
        return new \Saft\Backend\Redland\Rdf\NamedNode($uri);
    }
}