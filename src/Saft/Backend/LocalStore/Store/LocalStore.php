<?php

namespace Saft\Backend\LocalStore\Store;

use Saft\Store\StoreInterface;
use Saft\Store\AbstractTriplePatternStore;
use Saft\Rdf\StatementIterator;
use Saft\Rdf\Statement;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Filicious\Filesystem;
use Filicious\Local\LocalAdapter;
use Filicious\File;

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
        //TODO Log handler should be configurable
        $this->log->pushHandler(new StreamHandler('php://output'));

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

    /**
     * {@inheritdoc}
     */
    public function addStatements(StatementIterator $statements, $graphUri = null, array $options = array())
    {
        throw new \Exception('Unsupported Operation');
    }

    /**
     * {@inheritdoc}
     */
    public function deleteMatchingStatements(Statement $statement, $graphUri = null, array $options = array())
    {
        throw new \Exception('Unsupported Operation');
    }

    /**
     * {@inheritdoc}
     */
    public function getMatchingStatements(Statement $Statement, $graphUri = null, array $options = array())
    {
        throw new \Exception('Unsupported Operation');
    }

    /**
     * {@inheritdoc}
     */
    public function hasMatchingStatement(Statement $Statement, $graphUri = null, array $options = array())
    {
        throw new \Exception('Unsupported Operation');
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
