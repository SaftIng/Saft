<?php
namespace Saft\Addition\FileStore\Test;

use Saft\Addition\FileStore\Store\FileStore;
use Saft\Store\Test\StoreAbstractTest;
use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\StatementFactoryImpl;

class FileStoreTest extends StoreAbstractTest
{
    public function setUp()
    {
        $tempDirectory = tempnam(sys_get_temp_dir(), 'FileStore');
        $this->fixture = new FileStore($tempDirectory, new NodeFactoryImpl(), new StatementFactoryImpl());

        parent::setUp();
    }
}
