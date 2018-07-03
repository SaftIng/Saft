<?php

/*
 * This file is part of Saft.
 *
 * (c) Konrad Abicht <hi@inspirito.de>
 * (c) Natanael Arndt <arndt@informatik.uni-leipzig.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Saft\Store\Test;

use Saft\Rdf\Test\TestCase;
use Saft\Store\Store;

/**
 * Provides basic tests to check Store interface
 */
class StoreTest extends TestCase
{
    public function setUp()
    {
        $this->fixture = $this->getMockBuilder('Saft\Store\Store')->getMock();
    }

    public function testAllFunctionsAvailable()
    {
        // Statement related
        $this->assertTrue(\method_exists($this->fixture, 'addStatements'));
        $this->assertTrue(\method_exists($this->fixture, 'getMatchingStatements'));
        $this->assertTrue(\method_exists($this->fixture, 'hasMatchingStatement'));
        $this->assertTrue(\method_exists($this->fixture, 'deleteMatchingStatements'));

        // graph related
        $this->assertTrue(\method_exists($this->fixture, 'createGraph'));
        $this->assertTrue(\method_exists($this->fixture, 'dropGraph'));
        $this->assertTrue(\method_exists($this->fixture, 'getGraphs'));

        $this->assertTrue(\method_exists($this->fixture, 'query'));
    }
}
