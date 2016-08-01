<?php

namespace Saft\Skeleton\PropertyHelper;

use Zend\Cache\StorageFactory;
use Saft\Rdf\NamedNode;
use Saft\Rdf\NamedNodeImpl;
use Saft\Store\Store;


/**
 * Encapsulates PropertyHelper related classes, ensures correct usage and helps users that way
 * to use this stuff properly.
 */
class RequestHandler
{
    /**
     * @var Cache
     */
    protected $cache;

    /**
     * @var NamedNode
     */
    protected $graph;

    /**
     * @var AbstractIndex
     */
    protected $index;

    /**
     * @var Store
     */
    protected $store;

    /**
     * @param Store $store
     * @param NamedNode $graph Instance of the graph, whose ressources will be collected for the index
     */
    public function __construct(Store $store, NamedNode $graph)
    {
        $this->graph = $graph;
        $this->store = $store;
    }

    /**
     * @return array Array of string containing available property types.
     */
    public function getAvailableCacheBackends()
    {
        return array(
            'apc', 'filesystem', 'memcached', 'memory', 'mongodb', 'redis'
        );
    }

    /**
     * @return array Array of string containing available property types.
     */
    public function getAvailableTypes()
    {
        return array(
            'title'
        );
    }

    /**
     * @return Cache
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * @param string $action
     * @param array $payload Neccessary configuration to execute the requested action.
     * @param string $preferedLanguage Prefered language for the fetched titles
     */
    public function handle($action, $payload = array(), $preferedLanguage = "")
    {
        if (null == $this->index) {
            throw new \Exception('Please call setType before handle to initialize the index.');
        }

        $action = strtolower($action);

        /*
         * create index for all resources of a graph
         */
        if ('createindex' == $action) {
            return $this->index->createIndex();

        } elseif('fetchvalues' == $action) {
            return $this->index->fetchValues($payload, $preferedLanguage);
        }

        throw new \Exception('Unknown $action given: '. $action);
    }

    /**
     * Initializes the cache backend and storage.
     *
     * Configuration information (besides name) for each backend:
     *
     * - filesystem
     *   - dir - Path to the store where the data to be stored.
     *
     * - apc - No additional configuration needed.
     *
     * - memcached
     *   - host - Host of the memcached server.
     *   - port - Port of the memcached server.
     *
     * - mongodb
     *   - host - Host of the MongoDB server.
     *
     * - redis
     *   - host - Host of the memcached server.
     *   - port - Port of the memcached server.
     *
     * - memory - No additional configuration needed.
     *
     *
     * @param array $configuration
     * @throws \Exception if parameter $configuration is empty
     * @throws \Exception if parameter $configuration does not have key "name" set
     * @throws \Exception if an unknown name was given.
     */
    public function setupCache(array $configuration)
    {
        if (0 == count(array_keys($configuration))) {
            throw new \Exception('Parameter $configuration must not be empty.');
        } elseif (false === isset($configuration['name'])) {
            throw new \Exception('Parameter $configuration does not have key "name" set.');
        }

        switch ($configuration['name']) {
            case 'apc':
                $options = array();
                break;

            case 'apcu':
                $options = array();
                break;

            case 'filesystem':
                $options = array(
                    'cache_dir' => $configuration['dir'],
                    'key_pattern' => '/.*/'
                );
                break;

            case 'memcached':
                $options = array(
                    'servers' => array([
                        'host' => $configuration['host'],
                        'port' => $configuration['port']
                    ])
                );
                break;

            case 'memory':
                $options = array();
                break;

            case 'mongodb':
                $options = array(
                    'server' => $configuration['host'],
                    'database' => 'zend',
                    'collection' => 'cache'
                );
                break;

            case 'redis':
                $options = array(
                    'server' => array(
                        'host' => $configuration['host'],
                        'port' => $configuration['port']
                ));
                break;

            default:
                throw new \Exception('Unknown cache name given: '. $configuration['name']);
        }

        $this->cache = StorageFactory::factory(
            array(
                'adapter' => array(
                    'name' => $configuration['name'],
                    'options' => $options
        )));
    }

    /**
     * Based on given type, according index will be setup.
     *
     * @param string $type Type of the property, e.g. title. Check getAvailableTypes for more information.
     * @throws \Exception if unknown type was given.
     */
    public function setType($type)
    {
        if (null == $this->cache) {
            throw new \Exception('Please call setupCache before setType to initialize the cache environment.');
        }

        // type recognized
        if (in_array($type, $this->getAvailableTypes())) {
            switch($type) {
                case 'title':
                    $this->index = new TitleHelperIndex($this->cache, $this->store, $this->graph);
                    return;
            }
        }

        throw new \Exception('Unknown type given: '. $type);
    }

}
