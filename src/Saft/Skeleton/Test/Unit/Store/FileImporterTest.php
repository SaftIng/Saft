<?php

namespace Saft\Skeleton\Test\Unit\Store;

use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\StatementFactoryImpl;
use Saft\Rdf\StatementIteratorFactoryImpl;
use Saft\Skeleton\Store\FileImporter;
use Saft\Sparql\Query\QueryFactoryImpl;
use Saft\Store\BasicTriplePatternStore;
use Saft\Skeleton\Test\TestCase;

class FileImporterTest extends TestCase
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

        $this->fixture = new FileImporter($this->store);
    }

    /*
     * Tests for importFile
     */

    public function testImportFileFileHandleGiven()
    {
        $file = fopen(__DIR__ . '/../../assets/bsbm-dataset-5-products.nt', 'r');

        $this->assertEquals(3239, $this->fixture->importFile($file, $this->testGraph));
    }

    public function testImportFileFilenameGiven()
    {
        $file = __DIR__ . '/../../assets/bsbm-dataset-5-products.nt';

        $this->assertEquals(3239, $this->fixture->importFile($file, $this->testGraph));
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

        $file = fopen($filepath, 'r');

        $this->setExpectedException('\Exception');

        $this->fixture->importFile($file, $this->testGraph);
    }
}
