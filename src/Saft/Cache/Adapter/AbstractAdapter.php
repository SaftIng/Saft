<?php

namespace Saft\Cache\Adapter;

abstract class AbstractAdapter
{
    /**
     * Checks that all requirements for this adapter are fullfilled. 
     * 
     * @return boolean Returns true if all requirements are fullfilled.
     * @throws \Exception If one requirement is not fullfilled an exception will be thrown.
     */
    //public abstract function checkRequirements();
    
    /**
     * Init cache adapter. It should call checkRequirements to be sure all requirements
     * are fullfilled, before init anything.
     * 
     * @throws \Exception If checkRequirements is getting called, it can throw exceptions.
     */
    //public abstract function init(array $config);
}
