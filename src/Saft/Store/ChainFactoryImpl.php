<?php

namespace Saft\Store;

use Saft\Store\Exception\StoreException;

class ChainFactoryImpl implements ChainFactory
{
    /**
     * Creates a chain of Store instances which are coupled using their setSuccessor method.
     *
     * @param  array          $config Configuration array which contains a list of configuration arrays which
     *                        setup a certain Store instance.
     * @return Store          Instance of Store which was setup with successors.
     * @throws StoreException If an instance was created which does not implement ChainableStore interface.
     */
    public function createStoreChain(array $config)
    {
        if (1 > count($config)) {
            throw new StoreException('Empty $config parameter given.');
        }

        $storeFactory = new StoreFactoryImpl();

        $firstInstance = null;
        $currentInstance = null;
        $lastInstance = null;

        // go through each entry of $config. we assume here that each entry is a configuration array for a certain
        // Store implementation.
        foreach ($config as $storeInstanceConfig) {
            $currentInstance = $storeFactory->createInstance($storeInstanceConfig);

            // we force here, that the current instance created implements ChainableStore interface. the reason for
            // that is, that it forces the setSuccessor method to be implemented.
            if (false === $currentInstance instanceof ChainableStore) {
                throw new StoreException(
                    'Current instance does not implement Saft\\Store\\ChainableStore interface: '.
                    $storeInstanceConfig['class']
                );
            }

            // save first instance
            if (null === $lastInstance) {
                $firstInstance = $currentInstance;
            } else {
                // set successor for the last instance to $currentInstance
                $lastInstance->setSuccessor($currentInstance);
            }
        }

        return $firstInstance;
    }
}
