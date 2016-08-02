<?php

namespace Saft\Skeleton\Test;

use Saft\Rdf\NamedNodeImpl;
use Saft\Test\TestCase as SaftTestTestCase;

class TestCase extends SaftTestTestCase
{
    public function setUp()
    {
        $this->testGraph = new NamedNodeImpl('http://localhost/Saft/TestGraph/');

        // set path to test file
        $rootDir = dirname(__FILE__) . '/../../../../';
        $configFilepath = $rootDir . 'test-config.yml';

        $this->loadTestConfiguration($configFilepath);
    }
}
