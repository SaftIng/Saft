<?php

namespace Saft\Backend\LocalStore\Store;

use Saft\Store\StoreInterface;
use Saft\Store\AbstractTriplePatternStore;
use Saft\Rdf\StatementIterator;
use Saft\Rdf\Statement;
use Monolog\Logger;
use Filicious\Filesystem;
use Filicious\Local\LocalAdapter;
use Filicious\File;
use Monolog\Handler\NullHandler;

/**
 * Simple file based store working in a single directory. A .store file in the
 * directory is used to hold the meta deta.
 */
class LocalStore extends AbstractTriplePatternStore
{
    private $initialized = false;
    protected $log;
    protected $baseDir;
    protected $fileSystem;
    private $graphUriFileMapping;

    public function __construct($baseDir)
    {
        if (is_null($baseDir)) {
            throw new \InvalidArgumentException('$baseDir is null');
        }

        $className = get_class($this);
        $this->log = new Logger($className);
        //TODO Log Handler
        // $this->log->pushHandler(new StreamHandler('php://output'));
        $this->log->pushHandler(new NullHandler());

        $this->baseDir = $baseDir;
        $this->fileSystem = new Filesystem(new LocalAdapter($baseDir));
        $this->log->info('Using base dir: ' . $baseDir);

        $this->graphUriFileMapping = array();
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailableGraphs()
    {
        $this->ensureInitialized();
        return array_keys($this->graphUriFileMapping);
    }

    public function isGraphAvailable($uri)
    {
        if (!Util::isValidUri($uri)) {
            throw new \InvalidArgumentException(
                '$uri ' . $uri . ' is not valid'
            );
        }
        $this->ensureInitialized();
        return array_key_exists($uri, $this->graphUriFileMapping);
    }

    /**
     * Adds an empty graph with the given URI to store. This graph
     * is available after.
     * @param string $uri URI of the graph
     * @param string $filename filename relative to the base directory
     */
    public function addGraph($uri, $path)
    {
        if ($this->isGraphAvailable($uri)) {
            return;
        }

        $this->graphUriFileMapping[$uri] =
            $this->fileSystem->getFile($path);
        $this->graphUriFileMapping[$uri]->createFile();
        $this->saveStoreInfo();
        $this->log->addInfo('Graph <' . $uri . '> added');
    }

    /**
     * {@inheritdoc}
     */
    public function addStatements(StatementIterator $statements, $graphUri = null, array $options = array())
    {
        // This method have to handle multiple graphs.
        // Keep graph file open until the graph uri changes.
        $prevGraphUri = null;
        $pointer = null;
        foreach ($statements as $statement) {
            self::ensureStatementIsConcrete($statement);
            $resolvedUri = $this->resolveGraphUri($graphUri, $statement);
            // If graph uri changed, close the old graph file and open the new file.
            if ($resolvedUri != $prevGraphUri) {
                if (!is_null($pointer)) {
                    fclose($pointer);
                }
                $this->createGraphIfNotExists($resolvedUri);
                $graphFile = $this->getGraphFile($resolvedUri);
                $filename = Util::getAbsolutePath($this->baseDir, $graphFile->getPathname());
                $size = filesize($filename);
                $pointer = fopen($filename, 'a');
                // ftell returns always 0, whetever the file is opened in 'a' and is not empty
                $wasEmpty = ($size == 0);
            }
            // Don't add newline before, if the was empty
            // (If there was a newline at the end, it will be overriden)
            $line = ($wasEmpty ? "" : "\n") . NtriplesSerializer::serializeStatement($statement);
            fwrite($pointer, $line);
        }
        if (is_null($pointer)) {
            fclose($pointer);
        }
    }

    private static function ensureStatementIsConcrete(Statement $statement)
    {
        if (!$statement->isConcrete()) {
            throw new \InvalidArgumentException('Statement that is not concrete');
        }
    }

    private function createGraphIfNotExists($uri)
    {
        if (!$this->isGraphAvailable($uri)) {
            // TODO Replace with filename factory
            $path = basename($uri) . '.nt';
            $this->addGraph($uri, $path);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteMatchingStatements(Statement $pattern, $graphUri = null, array $options = array())
    {
        $graphUri = $this->resolveGraphUri($graphUri, $pattern);
        $graphFile = $this->getGraphFile($graphUri);

        throw new \Exception('Unsupported Operation');
    }

    /**
     * {@inheritdoc}
     * @return MatchingStatementIterator
     */
    public function getMatchingStatements(Statement $pattern, $graphUri = null, array $options = array())
    {
        $graphUri = $this->resolveGraphUri($graphUri, $pattern);
        $graphFile = $this->getGraphFile($graphUri);
        return new MatchingStatementIterator($graphFile, $pattern);
    }

    /**
     * {@inheritdoc}
     */
    public function hasMatchingStatement(Statement $pattern, $graphUri = null, array $options = array())
    {
        $it = $this->getMatchingStatements($pattern, $graphUri);
        $it->rewind();
        $found = $it->valid();
        $it->close();
        return $found;
    }

    protected function resolveGraphUri($graphUri, Statement $statement)
    {
        if (is_null($graphUri)) {
            if (!$statement->isQuad()) {
                throw new \InvalidArgumentException(
                    'Graph URI is not specified. '
                    . '$graphUri is null and $statement is not a quad.'
                );
            } elseif (!$statement->getGraph()->isConcrete()) {
                throw new \InvalidArgumentException('Graph is not concrete');
            }
            return $statement->getGraph()->getValue();
        }
        return $graphUri;
    }

    protected function getGraphFile($graphUri)
    {
        if (!$this->isGraphAvailable($graphUri)) {
            throw new \Exception(
                'Graph with uri ' . $graphUri . ' is not available'
            );
        }
        $graphFile = $this->graphUriFileMapping[$graphUri];
        return $graphFile;
    }

    /**
     * {@inheritdoc}
     */
    public function getStoreDescription()
    {
        throw new \Exception('Unsupported Operation');
    }

    /**
     * {@inheritdoc}
     */
    public function setChainSuccessor(StoreInterface $successor)
    {
        throw new \Exception('Unsupported Operation');
    }

    public function isInitialized()
    {
        return $this->initialized;
    }

    
    protected function ensureInitialized()
    {
        if (!$this->initialized) {
            throw new \LogicException('Not initialized');
        }
    }

    public function initialize()
    {
        if ($this->isInitialized()) {
            return;
        }

        $this->ensureBaseDirIsReadable();
        if ($this->isBaseDirInitialized()) {
            $this->loadStoreInfo();
        } else {
            $this->log->info('No .store file was found. '
                . 'Initializing base dir for the first time');
            $this->saveStoreInfo();
        }

        $this->initialized = true;
        $this->log->info('Initialized');
    }

    private function ensureBaseDirIsReadable()
    {
        $baseDir = $this->fileSystem->getFile('.');
        if (!$baseDir->isDirectory()) {
            throw new \Exception('Base dir is not a directory: ');
        } elseif (!$baseDir->isReadable()) {
            throw new \Exception('Base dir is not readable');
        }
    }

    private function isBaseDirInitialized()
    {
        $storeFile = $this->getStoreFile();
        return $storeFile->exists();
    }

    private function getStoreFile()
    {
        return $this->fileSystem->getFile('.store');
    }

    protected function loadStoreInfo(File $jsonFile = null)
    {
        if (is_null($jsonFile)) {
            $jsonFile = $this->getStoreFile();
        }

        $json = $jsonFile->getContents();
        $content = json_decode($json, true);
        if (is_null($content)) {
            throw new \Exception('.store file seems to be corrupted');
        }
        static::checkStoreInfo($content);
        foreach ($content['mapping'] as $uri => $path) {
            $this->graphUriFileMapping[$uri] =
                $this->fileSystem->getFile($path);
        }
        // Load more meta information here
    }

    protected static function checkStoreInfo($content)
    {
        if (!array_key_exists('mapping', $content)) {
            throw new \Exception('Key mapping not found');
        } elseif (!is_array($content['mapping'])) {
            throw new \Exception('mapping is not an array');
        } else {
            // Check all URIs in the mapping
            foreach ($content['mapping'] as $uri => $path) {
                if (!Util::isValidUri($uri)) {
                    throw new \Exception('Graph URI ' . $uri
                        . ' is not a valid uri');
                } elseif (!is_string($path)) {
                    throw new \Exception('Path for uri ' . $uri
                        . ' is not a string');
                }
            }
        }
    }

    protected function saveStoreInfo(File $jsonFile = null)
    {
        if (is_null($jsonFile)) {
            $jsonFile = $this->getStoreFile();
        }

        $mapping = array();
        foreach ($this->graphUriFileMapping as $graphUri => $file) {
            $mapping[$graphUri] = $file->getPathname();
        }
        $content = array(
            // Add more meta information here
            'mapping' => $mapping
        );
        $json = json_encode($content, JSON_PRETTY_PRINT
            | JSON_UNESCAPED_SLASHES | JSON_FORCE_OBJECT);
        $jsonFile->setContents($json);
    }
}
