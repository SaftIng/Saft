<?php

namespace Saft\Skeleton\DependencyInjection;

interface Container
{
    /**
     * Creates and returns an instance of a given class name.
     *
     * @param  string     $classToInstantiate Name of the class you want to instantiate.
     * @param  array      $parameter          Array which contains all parameter for the class to instantiate.
     *                                        (optional)
     * @return object     Instance of the given class, if available.
     * @throws \Exception If class to instantiate was not found.
     */
    public function createInstanceOf($classToInstantiate, array $parameter = array());
}
