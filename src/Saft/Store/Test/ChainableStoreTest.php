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
use Saft\Store\ChainableStore;

/**
 * Provides basic tests to check Store interface
 */
class ChainableStoreTest extends TestCase
{
    public function setUp()
    {
        $this->fixture = $this->getMockBuilder('Saft\Store\ChainableStore')->getMock();
    }

    public function testAllFunctionsAvailable()
    {
        // Statement related
        $this->assertTrue(\method_exists($this->fixture, 'setChainSuccessor'));
    }
}
