<?php

namespace Saft\Backend\HttpStore\Test;

use Saft\Cache\Cache;
use Saft\Backend\HttpStore\Store\Http;
use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\LiteralImpl;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\StatementImpl;
use Saft\Test\TestCase;
use Symfony\Component\Yaml\Parser;

class ClientUnitTest extends TestCase
{
    /**
     * Tests existence (simple)
     */
    public function testExistence()
    {
        $this->assertTrue(class_exists('\Saft\Backend\HttpStore\Net\Client'));
    }
}
