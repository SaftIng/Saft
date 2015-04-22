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

    public function testConstructorChecksBaseDirForNull()
    {
        $this->setExpectedException('\Exception');
        new LocalStore(null);
    }

    public function testInitializeChecksIfBaseDirExists()
    {
        $this->tempDirectory = TestUtil::createTempDirectory();
        // Non existing directory
        $nonExisting = $this->tempDirectory . DIRECTORY_SEPARATOR
            . 'NonExisting';
        assert(!is_dir($nonExisting));

        $store = new LocalStore($nonExisting);
        // Should fail in order of the non-existing base dir
        $this->setExpectedException('\Exception');
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

    public function testGetAvailableGraphsChecksIfInitialized()
    {
        $this->tempDirectory = TestUtil::createTempDirectory();
        $store = new LocalStore($this->tempDirectory);
        // Fails because intiailize was not called before
        // TODO is this the intended behavior? Can't this be done in the constructor
        $this->setExpectedException('\LogicException');
        $store->getAvailableGraphs();
    }

    public function testIsGraphAvailableChecksIfInitialized()
    {
        $this->tempDirectory = TestUtil::createTempDirectory();
        $store = new LocalStore($this->tempDirectory);
        // Fails because intiailize was not called before
        // TODO is this the intended behavior? Can't this be done in the constructor
        $this->setExpectedException('\LogicException');
        $store->isGraphAvailable(new NamedNodeImpl('http://foo.org/'));
    }

    public function testHasMatchingStatementChecksIfInitialized()
    {
        $this->tempDirectory = TestUtil::createTempDirectory();
        $store = new LocalStore($this->tempDirectory);
        $pattern = self::createAllPattern();
        // Fails because intiailize was not called before
        // TODO is this the intended behavior? Can't this be done in the constructor
        $this->setExpectedException('\LogicException');
        $store->hasMatchingStatement($pattern, new NamedNodeImpl('http://localhost:8890/foaf'));
    }

    public function testGetMatchingStatementsChecksIfInitialized()
    {
        $this->tempDirectory = TestUtil::createTempDirectory();
        $store = new LocalStore($this->tempDirectory);
        $pattern = self::createAllPattern();
        // Fails because intiailize was not called before
        // TODO is this the intended behavior? Can't this be done in the constructor
        $this->setExpectedException('\LogicException');
        $store->getMatchingStatements($pattern);
    }

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
        // Fails because intiailize was not called before
        // TODO is this the intended behavior? Can't this be done in the constructor
        $this->setExpectedException('\LogicException');
        $store->addStatements($statements, new NamedNodeImpl('http://localhost:8890/foaf'));
    }

    public function testDeleteMatchingStatementsChecksIfInitialized()
    {
        $this->tempDirectory = TestUtil::createTempDirectory();
        $store = new LocalStore($this->tempDirectory);
        $pattern = self::createAllPattern();
        // Fails because intiailize was not called before
        // TODO is this the intended behavior? Can't this be done in the constructor
        $this->setExpectedException('\LogicException');
        $store->deleteMatchingStatements($pattern, new NamedNodeImpl('http://localhost:8890/foaf'));
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

    public function testIsGraphAvailable()
    {
        $this->tempDirectory = TestUtil::createTempDirectory();
        $this->writeStoreFile($this->tempDirectory, self::STORE_FILE_CONTENT);

        $store = new LocalStore($this->tempDirectory);
        $store->initialize();
        $this->assertTrue($store->isGraphAvailable(new NamedNodeImpl('http://localhost:8890/foo')));
        $this->assertTrue($store->isGraphAvailable(new NamedNodeImpl('http://dbpedia.org/data/ireland')));
        $this->assertFalse($store->isGraphAvailable(new NamedNodeImpl('http://non.existing/graph')));
    }

    public function testAddGraph()
    {
        $this->tempDirectory = TestUtil::createTempDirectory();
        $srcDir = $this->getFixtureDir();
        $dstDir = $this->tempDirectory;
        TestUtil::copyDirectory($srcDir, $dstDir);

        $store = new LocalStore($this->tempDirectory);
        $store->initialize();
        $graph = new NamedNodeImpl('http://localhost:8890/bar');
        $path = 'bar.nt';
        $this->assertFalse($store->isGraphAvailable($graph));
        $store->addGraph($graph, $path);
        $this->assertTrue($store->isGraphAvailable($graph));
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
        $it = $store->getMatchingStatements($pattern, new NamedNodeImpl('http://localhost:8890/foaf'));
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
        $it = $store->getMatchingStatements($pattern, new NamedNodeImpl('http://localhost:8890/foaf'));
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
            new NamedNodeImpl('http://localhost:8890/foaf')
        ));

        $pattern = new StatementImpl(
            new NamedNodeImpl('http://notexist/'),
            new VariableImpl('?p'),
            new VariableImpl('?o')
        );
        $this->assertFalse($store->hasMatchingStatement(
            $pattern,
            new NamedNodeImpl('http://localhost:8890/foaf')
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

        $preCount = $this->countStatements($store, new NamedNodeImpl('http://localhost:8890/foaf'));
        $statement = new StatementImpl(
            new BlankNodeImpl('genid1'),
            new NamedNodeImpl('http://example.net'),
            new BlankNodeImpl('genid2')
        );
        $statements = new ArrayStatementIteratorImpl([$statement]);
        $store->addStatements($statements, new NamedNodeImpl('http://localhost:8890/foaf'));
        $postCount = $this->countStatements($store, new NamedNodeImpl('http://localhost:8890/foaf'));
        $this->assertEquals($preCount + 1, $postCount);

        $this->assertFalse($store->isGraphAvailable(new NamedNodeImpl('http://localhost:8890/baz')));
        $statement = new StatementImpl(
            new BlankNodeImpl('genid1'),
            new NamedNodeImpl('http://example.net'),
            new BlankNodeImpl('genid2'),
            new NamedNodeImpl('http://localhost:8890/baz')
        );
        $statements = new ArrayStatementIteratorImpl([$statement]);
        $store->addStatements($statements);
        $this->assertTrue($store->isGraphAvailable(new NamedNodeImpl('http://localhost:8890/baz')));

        $it = $store->getMatchingStatements(
            self::createAllPattern(),
            new NamedNodeImpl('http://localhost:8890/baz')
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
        $pattern = new StatementImpl(
            new BlankNodeImpl('genid1'),
            new VariableImpl('?p'),
            new VariableImpl('?o')
        );

        // Count how many statements matches the pattern
        $it = $store->getMatchingStatements($pattern, new NamedNodeImpl('http://localhost:8890/foaf'));
        $numMatches = 0;
        foreach ($it as $statement) {
            $numMatches++;
        }
        $it->close();
        $this->assertEquals(3, $numMatches);

        // Delete matching statements
        $preCount = $this->countStatements($store, new NamedNodeImpl('http://localhost:8890/foaf'));
        $store->deleteMatchingStatements($pattern, new NamedNodeImpl('http://localhost:8890/foaf'));
        $postCount = $this->countStatements($store, new NamedNodeImpl('http://localhost:8890/foaf'));
        $this->assertEquals(3, $preCount - $postCount);

        // Check, that all matches has been deleted
        $it = $store->getMatchingStatements($pattern, new NamedNodeImpl('http://localhost:8890/foaf'));
        $numMatches = 0;
        foreach ($it as $statement) {
            $numMatches++;
        }
        $it->close();
        $this->assertEquals(0, $numMatches);
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
