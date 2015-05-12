<?php

namespace Saft\Store;

use Saft\Store\Exception\StoreException;
use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\StatementFactoryImpl;

class StoreFactoryImpl implements StoreFactory
{
    /**
     * Creates an instance of Store using given configuration.
     *
     * @param  array $config
     * @return Store Instance of Store
     * @throws StoreException If no class to instantiate was given.
     * @throws StoreException If given class was not found.
     */
    public function createInstance(array $config)
    {
        if (isset($config['class']) && class_exists($config['class'])) {
            $class = $config['class'];
            $nodeFactory = new NodeFactoryImpl();
            $statementFactory = new StatementFactoryImpl();
            return new $class($nodeFactory, $statementFactory, $config);

        } else {
            throw new StoreException('No class information given or PHP class does not exists.');
        }
    }
}
