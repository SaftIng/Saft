<?php
namespace Saft\Backend\LocalStore\Test;

use Saft\Backend\LocalStore\Store\LocalStore;
use Saft\Rdf\StatementImpl;
use Saft\Rdf\VariableImpl;
use Saft\Rdf\BlankNodeImpl;
use Saft\Rdf\BlankNode;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\LiteralImpl;
use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\Statement;

class LocalStoreUnitTest extends \PHPUnit_Framework_TestCase
{
    const STORE_FILE_CONTENT = <<<EOD
{
    "mapping": {
        "http://localhost:8890/foo": "/foaf.nt",
        "http://dbpedia.org/data/ireland": "/ireland.nt"
    }
}
EOD;

    // Used for temporary base dirs
    protected $tempDirectory = null;

    public function tearDown()
    {
        if (!is_null($this->tempDirectory)) {
            TestUtil::deleteDirectory($this->tempDirectory);
        }
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConstructorChecksBaseDirForNull()
    {
        new LocalStore(null);
    }

    /**
     * @expectedException \Exception
     */
    public function testInitializeChecksIfBaseDirExists()
    {
        $this->tempDirectory = TestUtil::createTempDirectory();
        // Non existing directory
        $nonExisting = $this->tempDirectory . DIRECTORY_SEPARATOR
            . 'NonExisting';
        assert(!is_dir($nonExisting));
        
        $store = new LocalStore($nonExisting);
        // Should fail in order of the non-existing base dir
        $store->initialize();
    }

    public function testInitializeCreatesStoreFile()
    {
        $this->tempDirectory = TestUtil::createTempDirectory();
        $store = new LocalStore($this->tempDirectory);
        $store->initialize();
        $storeFile = $this->tempDirectory . DIRECTORY_SEPARATOR
            . '.store';
        $this->assertTrue(is_file($storeFile));
    }

    public function testIsInitialized()
    {
        $this->tempDirectory = TestUtil::createTempDirectory();
        $store = new LocalStore($this->tempDirectory);
        $this->assertFalse($store->isInitialized());
        $store->initialize();
        $this->assertTrue($store->isInitialized());
    }

    /**
     * @expectedException \LogicException
     */
    public function testGetAvailableGraphsChecksIfInitialized()
    {
        $this->tempDirectory = TestUtil::createTempDirectory();
        $store = new LocalStore($this->tempDirectory);
        // Must intiailized before
        $store->getAvailableGraphs();
    }

    public function testNoAvailableGraphsAfterInitializingAnEmptyBaseDir()
    {
        $this->tempDirectory = TestUtil::createTempDirectory();
        $store = new LocalStore($this->tempDirectory);
        $store->initialize();
        $graphs = $store->getAvailableGraphs();
        $this->assertEmpty($graphs);
    }

    public function testGetAvailableGraphs()
    {
        $this->tempDirectory = TestUtil::createTempDirectory();
        $this->writeStoreFile($this->tempDirectory, self::STORE_FILE_CONTENT);

        $store = new LocalStore($this->tempDirectory);
        $store->initialize();
        $graphs = $store->getAvailableGraphs();
        $this->assertContains('http://localhost:8890/foo', $graphs);
        $this->assertContains('http://dbpedia.org/data/ireland', $graphs);
    }

    /**
     * @expectedException \LogicException
     */
    public function testIsGraphAvailableChecksIfInitialized()
    {
        $this->tempDirectory = TestUtil::createTempDirectory();
        $store = new LocalStore($this->tempDirectory);
        // Must intiailized before
        $store->isGraphAvailable('foo');
    }

    public function testIsGraphAvailable()
    {
        $this->tempDirectory = TestUtil::createTempDirectory();
        $this->writeStoreFile($this->tempDirectory, self::STORE_FILE_CONTENT);

        $store = new LocalStore($this->tempDirectory);
        $store->initialize();
        $this->assertTrue($store->isGraphAvailable('http://localhost:8890/foo'));
        $this->assertTrue($store->isGraphAvailable('http://dbpedia.org/data/ireland'));
        $this->assertFalse($store->isGraphAvailable('http://non.existing/graph'));
    }

    /**
     * @expectedException \LogicException
     */
    public function testHasMatchingStatementChecksIfInitialized()
    {
        $this->tempDirectory = TestUtil::createTempDirectory();
        $store = new LocalStore($this->tempDirectory);
        $pattern = self::createAllPattern();
        $store->hasMatchingStatement($pattern, 'http://localhost:8890/foaf');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHasMatchingStatementChecksIfGraphIsSpecified()
    {
        $this->tempDirectory = TestUtil::createTempDirectory();
        $store = new LocalStore($this->tempDirectory);
        $store->initialize();
        $pattern = self::createAllPattern();
        assert(!$pattern->isQuad());
        $store->hasMatchingStatement($pattern);
    }

    /**
     * @expectedException \LogicException
     */
    public function testGetMatchingStatementsChecksIfInitialized()
    {
        $this->tempDirectory = TestUtil::createTempDirectory();
        $store = new LocalStore($this->tempDirectory);
        $pattern = self::createAllPattern();
        $store->getMatchingStatements($pattern);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetMatchingStatementsChecksIfGraphIsSpecified()
    {
        $this->tempDirectory = TestUtil::createTempDirectory();
        $store = new LocalStore($this->tempDirectory);
        $store->initialize();
        $pattern = self::createAllPattern();
        assert(!$pattern->isQuad());
        $store->getMatchingStatements($pattern, null);
    }

    /**
     * @expectedException \LogicException
     */
    public function testAddStatementsChecksIfInitialized()
    {
        $this->tempDirectory = TestUtil::createTempDirectory();
        $store = new LocalStore($this->tempDirectory);
        $statement = new StatementImpl(
            new BlankNodeImpl('foo'),
            new NamedNodeImpl('http://bar'),
            new LiteralImpl('baz')
        );
        $statements = new ArrayStatementIteratorImpl([$statement]);
        $store->addStatements($statements, 'http://localhost:8890/foaf');
    }

    /**
     * @expectedException \LogicException
     */
    public function testAddStatementsChecksIfGraphIsSpecified()
    {
        $this->tempDirectory = TestUtil::createTempDirectory();
        $store = new LocalStore($this->tempDirectory);
        $store->initialize();
        $statement = new StatementImpl(
            new BlankNodeImpl('foo'),
            new NamedNodeImpl('http://bar'),
            new LiteralImpl('baz')
        );
        assert(!$statement->isQuad());
        $statements = new ArrayStatementIteratorImpl([$statement]);
        $store->addStatements($statements, null);
    }

    /**
     * @expectedException \LogicException
     */
    public function testDeleteMatchingStatementsChecksIfInitialized()
    {
        $this->tempDirectory = TestUtil::createTempDirectory();
        $store = new LocalStore($this->tempDirectory);
        $pattern = self::createAllPattern();
        $store->deleteMatchingStatements($pattern, 'http://localhost:8890/foaf');
    }

    /**
     * @expectedException \LogicException
     */
    public function testDeleteMatchingStatementsChecksIfGraphIsSpecified()
    {
        $this->tempDirectory = TestUtil::createTempDirectory();
        $store = new LocalStore($this->tempDirectory);
        $store->initialize();
        $pattern = self::createAllPattern();
        assert(!$pattern->isQuad());
        $store->deleteMatchingStatements($pattern, null);
    }

    public function testAddGraph()
    {
        $this->tempDirectory = TestUtil::createTempDirectory();
        $srcDir = $this->getFixtureDir();
        $dstDir = $this->tempDirectory;
        TestUtil::copyDirectory($srcDir, $dstDir);

        $store = new LocalStore($this->tempDirectory);
        $store->initialize();
        $uri = 'http://localhost:8890/bar';
        $path = 'bar.nt';
        $this->assertFalse($store->isGraphAvailable($uri));
        $store->addGraph($uri, $path);
        $this->assertTrue($store->isGraphAvailable($uri));
        $this->assertFileExists($this->tempDirectory . DIRECTORY_SEPARATOR . $path);
    }

    public function testGetMatchingStatements()
    {
        $this->tempDirectory = TestUtil::createTempDirectory();
        $srcDir = $this->getFixtureDir();
        $dstDir = $this->tempDirectory;
        TestUtil::copyDirectory($srcDir, $dstDir);

        $store = new LocalStore($this->tempDirectory);
        $store->initialize();
        $pattern = new StatementImpl(
            new BlankNodeImpl('genid1'),
            new VariableImpl('?p'),
            new VariableImpl('?o')
        );
        $it = $store->getMatchingStatements($pattern, 'http://localhost:8890/foaf');
        $matches = [];
        foreach ($it as $statement) {
            $this->assertTrue($statement->isConcrete());
            $this->assertTrue($statement->getSubject() instanceof BlankNode);
            $this->assertEquals('genid1', $statement->getSubject()->getBlankId());
            array_push($matches, $statement);
        }
        $it->close();
        $this->assertEquals(3, count($matches));

        $pattern = new StatementImpl(
            new NamedNodeImpl('http://notexist/'),
            new VariableImpl('?p'),
            new VariableImpl('?o')
        );
        $it = $store->getMatchingStatements($pattern, 'http://localhost:8890/foaf');
        $this->assertFalse($it->valid());
        $it->close();
    }

    public function testHasMatchingStatements()
    {
        $this->tempDirectory = TestUtil::createTempDirectory();
        $srcDir = $this->getFixtureDir();
        $dstDir = $this->tempDirectory;
        TestUtil::copyDirectory($srcDir, $dstDir);

        $store = new LocalStore($this->tempDirectory);
        $store->initialize();
        $pattern = new StatementImpl(
            new BlankNodeImpl('genid1'),
            new VariableImpl('?p'),
            new VariableImpl('?o')
        );
        $this->assertTrue($store->hasMatchingStatement(
            $pattern,
            'http://localhost:8890/foaf'
        ));

        $pattern = new StatementImpl(
            new NamedNodeImpl('http://notexist/'),
            new VariableImpl('?p'),
            new VariableImpl('?o')
        );
        $this->assertFalse($store->hasMatchingStatement(
            $pattern,
            'http://localhost:8890/foaf'
        ));
    }

    public function testAddStatements()
    {
        $this->tempDirectory = TestUtil::createTempDirectory();
        $srcDir = $this->getFixtureDir();
        $dstDir = $this->tempDirectory;
        TestUtil::copyDirectory($srcDir, $dstDir);

        $store = new LocalStore($this->tempDirectory);
        $store->initialize();

        $preCount = $this->countStatements($store, 'http://localhost:8890/foaf');
        $statement = new StatementImpl(
            new BlankNodeImpl('genid1'),
            new NamedNodeImpl('http://example.net'),
            new BlankNodeImpl('genid2')
        );
        $statements = new ArrayStatementIteratorImpl([$statement]);
        $store->addStatements($statements, 'http://localhost:8890/foaf');
        $postCount = $this->countStatements($store, 'http://localhost:8890/foaf');
        $this->assertEquals($preCount + 1, $postCount);

        $this->assertFalse($store->isGraphAvailable('http://localhost:8890/baz'));
        $statement = new StatementImpl(
            new BlankNodeImpl('genid1'),
            new NamedNodeImpl('http://example.net'),
            new BlankNodeImpl('genid2'),
            new NamedNodeImpl('http://localhost:8890/baz')
        );
        $statements = new ArrayStatementIteratorImpl([$statement]);
        $store->addStatements($statements);
        $this->assertTrue($store->isGraphAvailable('http://localhost:8890/baz'));

        $it = $store->getMatchingStatements(
            self::createAllPattern(),
            'http://localhost:8890/baz'
        );
        $it->rewind();
        $this->assertTrue($it->valid());
        $match = $it->current();
        $it->close();
        $this->assertTrue($statement->matches($match));
    }

    public function testDeleteMatchingStatements()
    {
        $this->tempDirectory = TestUtil::createTempDirectory();
        $srcDir = $this->getFixtureDir();
        $dstDir = $this->tempDirectory;
        TestUtil::copyDirectory($srcDir, $dstDir);

        $store = new LocalStore($this->tempDirectory);
        $store->initialize();
        $preCount = $this->countStatements($store, 'http://localhost:8890/foaf');
        $pattern = new StatementImpl(
            new BlankNodeImpl('genid1'),
            new VariableImpl('?p'),
            new VariableImpl('?o')
        );
        $store->deleteMatchingStatements($pattern, 'http://localhost:8890/foaf');
        $postCount = $this->countStatements($store, 'http://localhost:8890/foaf');
        $this->assertEquals($preCount - 3, $postCount);
    }

    protected function countStatements(LocalStore $store, $uri = null)
    {
        $it = $store->getMatchingStatements(self::createAllPattern(), $uri);
        try {
            $count = 0;
            foreach ($it as $statement) {
                $count++;
            }
            $it->close();
            return $count;
        } catch (\Exception $e) {
            $it->close();
            throw $e;
        }
    }

    protected static function writeStoreFile($dir, $content)
    {
        $fileName = $dir . DIRECTORY_SEPARATOR . '.store';
        $file = fopen($fileName, 'w');
        if ($file === false) {
            throw new \Exception('Unable to write .store file ' . $fileName);
        }
        fwrite($file, $content);
        fclose($file);
    }

    protected static function createAllPattern()
    {
        $pattern = new StatementImpl(
            new VariableImpl('?s'),
            new VariableImpl('?p'),
            new VariableImpl('?o')
        );
        return $pattern;
    }

    private function getFixtureDir()
    {
        return __DIR__ . DIRECTORY_SEPARATOR
        . 'Fixture' . DIRECTORY_SEPARATOR . 'Store';
    }
}
