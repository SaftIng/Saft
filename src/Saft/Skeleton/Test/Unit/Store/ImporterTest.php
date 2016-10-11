<?php

namespace Saft\Skeleton\Test\Unit\Store;

use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\StatementFactoryImpl;
use Saft\Rdf\StatementIteratorFactoryImpl;
use Saft\Skeleton\Store\Importer;
use Saft\Sparql\Query\QueryFactoryImpl;
use Saft\Store\BasicTriplePatternStore;
use Saft\Skeleton\Test\TestCase;

class ImporterTest extends TestCase
{
    /**
     * @var Store
     */
    protected $store;

    public function setUp()
    {
        parent::setUp();

        // store
        $this->store = new BasicTriplePatternStore(
            new NodeFactoryImpl(),
            new StatementFactoryImpl(),
            new QueryFactoryImpl(),
            new StatementIteratorFactoryImpl()
        );

        $this->fixture = new Importer($this->store);
    }

    /*
     * Tests for importFile
     */

    // RDF/XML
    public function testImportXMLFileFilenameGiven()
    {
        $this->assertTrue($this->fixture->importFile(__DIR__ . '/../../assets/dbpedia-leipzig-part.rdf', $this->testGraph));
    }

    public function testImportFileInvalidFilenameGiven()
    {
        $filepath = sys_get_temp_dir() . '/saft-skeleton-file-importer.ttl';

        file_put_contents($filepath, '');

        $this->setExpectedException('\Exception');

        $this->fixture->importFile($filepath, $this->testGraph);
    }

    public function testImportFileInvalidFileContentGiven()
    {
        $filepath = sys_get_temp_dir() . '/saft-skeleton-file-importer';

        file_put_contents($filepath, '{');

        $this->setExpectedException('\Exception');

        $this->fixture->importFile($filepath, $this->testGraph);
    }
}
