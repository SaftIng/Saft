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

namespace Saft\Rdf\Test;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use Saft\Rdf\CommonNamespaces;
use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\RdfHelpers;
use Saft\Rdf\StatementFactoryImpl;
use Saft\Rdf\StatementIteratorFactoryImpl;

/**
 * @api
 *
 * @since 0.1
 */
abstract class TestCase extends PHPUnitTestCase
{
    /**
     * @var array
     */
    protected $configuration;

    protected $commonNamespaces;

    /**
     * Contains an instance of the class to test.
     *
     * @var mixed
     */
    protected $fixture;

    protected $nodeFactory;
    protected $rdfHelpers;
    protected $statementFactory;
    protected $statementIteratorFactory;

    /**
     * @var NamedNode
     */
    protected $testGraph;

    /**
     * @var string
     */
    protected $testGraphUri;

    /**
     * Place to setup stuff for Saft related tests.
     *
     * @api
     *
     * @since 0.1
     */
    public function setUp()
    {
        parent::setUp();

        $this->commonNamespaces = new CommonNamespaces();
        $this->rdfHelpers = new RdfHelpers();
        $this->nodeFactory = new NodeFactoryImpl($this->commonNamespaces);
        $this->statementFactory = new StatementFactoryImpl($this->rdfHelpers);
        $this->statementIteratorFactory = new StatementIteratorFactoryImpl();

        $this->testGraphUri = 'http://localhost/Saft/TestGraph/';
        $this->testGraph = $this->nodeFactory->createNamedNode($this->testGraphUri);
    }
}
