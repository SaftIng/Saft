<?php

namespace Saft\Store\Test;

use Saft\Test\TestCase;

abstract class StoreFactoryAbstractTest extends TestCase
{
    /**
     * An abstract method which returns the subject under test (SUT), in this case an instances of StoreFactory.
     *
     * @param  array        $configuration
     * @return StoreFactory The subject under test.
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
     * Tests createInstance
     */

    public function testCreateInstanceNoClassGiven()
    {
        $this->setExpectedException('Saft\Store\Exception\StoreException');

        $this->fixture = $this->newInstance();
        $this->fixture->createInstance(array());
    }

    public function testCreateInstanceNonExistingClassGiven()
    {
        $this->setExpectedException('Saft\Store\Exception\StoreException');

        $this->fixture = $this->newInstance();
        $this->fixture->createInstance(array('class' => 'not existing class'));
    }

    public function testCreateInstanceQueryCache()
    {
        if (false === isset($this->config['queryCacheConfig'])) {
            $this->markTestSkipped('No queryCacheConfig in test-config.yml set.');
        }

        $this->fixture = $this->newInstance();

        $this->fixture->createInstance($this->config['queryCacheConfig']);
    }
}
