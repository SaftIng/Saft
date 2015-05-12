<?php

namespace Saft\Store\Test;

use Saft\Store\ChainFactoryImpl;

class ChainFactoryImplTest extends ChainFactoryAbstractTest
{
    /**
     * A method which returns the subject under test (SUT), in this case an instances of ChainFactory.
     *
     * @param  array        $configuration
     * @return ChainFactory The subject under test.
     */
    public function newInstance()
    {
        return new ChainFactoryImpl();
    }
}
