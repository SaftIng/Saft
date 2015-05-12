<?php

namespace Saft\Store\Test;

use Saft\Store\StoreFactoryImpl;

class StoreFactoryImplTest extends StoreFactoryAbstractTest
{
    /**
     * An abstract method which returns the subject under test (SUT), in this case an instances of StoreFactory.
     *
     * @param  array        $config
     * @return StoreFactory The subject under test.
     */
    public function newInstance()
    {
        return new StoreFactoryImpl();
    }
}
