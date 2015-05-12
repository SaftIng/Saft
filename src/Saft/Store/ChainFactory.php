<?php

namespace Saft\Store;

interface ChainFactory
{
    /**
     * Creates a chain of Store instances which are coupled using their setSuccessor method.
     *
     * @param  array          $config Configuration array which contains a list of configuration arrays which
     *                        setup a certain Store instance.
     * @return Store          Instance of Store which was setup with successors.
     * @throws StoreException If an instance was created which does not implement ChainableStore interface.
     */
    public function createStoreChain(array $config);
}
