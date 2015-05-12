<?php

namespace Saft\Store\Test;

use Saft\Test\TestCase;

abstract class ChainFactoryAbstractTest extends TestCase
{
    /**
     * An abstract method which returns the subject under test (SUT), in this case an instances of ChainFactory.
     *
     * @param  array        $configuration
     * @return ChainFactory The subject under test.
     */
    abstract public function newInstance();

    /**
     * Setup subject under test.
     */
    public function setUp()
    {
        parent::setUp();

        $this->fixture = $this->newInstance();
    }

    /**
     * Tests createStoreChain
     */

    public function testCreateStoreChain()
    {
        $this->fixture = $this->newInstance();
        $this->fixture->createStoreChain(array(
            array('class' => 'Saft\\Store\\Test\\ChainableBasicSparqlStore'),
            array('class' => 'Saft\\Store\\Test\\ChainableBasicSparqlStore')
        ));
    }

    public function testCreateStoreChainEmptyConfigArray()
    {
        // expects exception, because empty array was given
        $this->setExpectedException('Saft\Store\Exception\StoreException');

        $this->fixture = $this->newInstance();
        $this->fixture->createStoreChain(array());
    }

    public function testCreateStoreChainWithUnchainableEntries()
    {
        // expects exception, because second class does not implement ChainableStore interface
        $this->setExpectedException('Saft\Store\Exception\StoreException');

        $this->fixture = $this->newInstance();
        $this->fixture->createStoreChain(array(
            array('class' => 'Saft\\Store\\Test\\ChainableBasicSparqlStore'),
            // this Store implementation does not implement ChainableStore interface
            array('class' => 'Saft\\Store\\Test\\BasicTriplePatternStore')
        ));
    }
}
